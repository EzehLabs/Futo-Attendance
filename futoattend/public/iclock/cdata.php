<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

function response_ok(string $message = 'OK'): void
{
    http_response_code(200);
    echo $message;
    exit;
}

function response_error(string $message): void
{
    http_response_code(400);
    echo $message;
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/*
 * ZKTeco first contacts this URL with GET.
 * We simply acknowledge it.
 */
if ($method === 'GET') {
    response_ok('OK');
}

/*
 * ZKTeco sends attendance records with:
 * POST /iclock/cdata?table=ATTLOG&SN=...
 */
if ($method !== 'POST') {
    response_error('Method not allowed');
}

$table = strtoupper(trim($_GET['table'] ?? ''));
$serial = trim($_GET['SN'] ?? '');

if ($table !== 'ATTLOG') {
    // Do not process OPERLOG/options/other tables.
    response_ok('OK');
}

if ($serial === '') {
    response_error('Missing device serial');
}

/*
 * Find the device using its serial number.
 */
$pdo = db();

$stmt = $pdo->prepare(
    'SELECT id FROM devices WHERE device_code = ? LIMIT 1'
);
$stmt->execute([$serial]);
$device = $stmt->fetch();

if (!$device) {
    response_error('Unknown device');
}

$deviceId = (int)$device['id'];

/*
 * Read the raw ATTLOG body.
 *
 * Typical ZKTeco line:
 * 1    2026-10-06 15:20:00    255    4    0
 */
$raw = file_get_contents('php://input');

if (trim($raw) === '') {
    response_ok('OK');
}

$lines = preg_split('/\r\n|\r|\n/', trim($raw));

$processed = 0;
$duplicates = 0;
$errors = 0;

foreach ($lines as $line) {

    $line = trim($line);

    if ($line === '') {
        continue;
    }

    /*
     * ZKTeco ATTLOG fields are tab separated.
     */
    $fields = preg_split('/\t+/', $line);

    if (count($fields) < 2) {
        $errors++;
        continue;
    }

    $deviceUserId = trim($fields[0]);
    $punchTime    = trim($fields[1]);

    if ($deviceUserId === '' || $punchTime === '') {
        $errors++;
        continue;
    }

    /*
     * Create a deterministic record key.
     *
     * This is important because ZKTeco may resend old
     * attendance records. The same punch must not create
     * another database record.
     */
    $recordKey = hash(
        'sha256',
        $serial . '|' . $deviceUserId . '|' . $punchTime
    );

    try {

        /*
         * Insert raw punch.
         *
         * We intentionally do NOT process OPERLOG data,
         * fingerprints, face templates, etc.
         */
        $stmt = $pdo->prepare(
            'INSERT INTO raw_punches
            (device_id, device_user_id, punch_time, device_record_key)
            VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $deviceId,
            $deviceUserId,
            $punchTime,
            $recordKey
        ]);

        $punchId = (int)$pdo->lastInsertId();

        /*
         * Update device heartbeat.
         */
        $pdo->prepare(
            'UPDATE devices SET last_seen_at = NOW() WHERE id = ?'
        )->execute([$deviceId]);

        /*
         * Match the punch against a class session.
         */
        $q = $pdo->prepare(
            'SELECT
                u.id AS student_id,
                s.id AS session_id,
                s.start_time,
                s.end_time,
                c.code
             FROM users u
             JOIN enrolments e ON e.student_id = u.id
             JOIN class_sessions s ON s.course_id = e.course_id
             JOIN courses c ON c.id = s.course_id
             WHERE u.login_id = ?
               AND u.role = "student"
               AND u.active = 1
               AND s.device_id = ?
               AND s.session_date = DATE(?)
               AND s.cancelled = 0
               AND TIME(?) BETWEEN s.start_time AND s.end_time
             ORDER BY s.id DESC
             LIMIT 1'
        );

        $q->execute([
            $deviceUserId,
            $deviceId,
            $punchTime,
            $punchTime
        ]);

        $match = $q->fetch();

        if ($match) {

            $late = (int)($pdo->query(
                "SELECT setting_value
                 FROM settings
                 WHERE setting_key = 'late_window_minutes'"
            )->fetchColumn() ?: 15);

            $punchDate = date('Y-m-d', strtotime($punchTime));

            $cutoff =
                strtotime($punchDate . ' ' . $match['start_time'])
                + ($late * 60);

            $status =
                strtotime($punchTime) <= $cutoff
                ? 'Present'
                : 'Late';

            $ins = $pdo->prepare(
                'INSERT IGNORE INTO attendance
                (session_id, student_id, status, punch_id, source)
                VALUES (?, ?, ?, ?, "DEVICE")'
            );

            $ins->execute([
                (int)$match['session_id'],
                (int)$match['student_id'],
                $status,
                $punchId
            ]);
        }

        $processed++;

    } catch (PDOException $e) {

        /*
         * Duplicate device_record_key means the device
         * resent an existing punch. That is safe to ignore.
         */
        if ($e->getCode() === '23000') {
            $duplicates++;
            continue;
        }

        $errors++;
    }
}

response_ok(
    "OK\nProcessed: {$processed}\nDuplicates: {$duplicates}\nErrors: {$errors}"
);