<?php
/**
 * FUTO Attendance - ZKTeco TCP diagnostic test.
 *
 * SAFE TEST:
 * - Connects to the device on TCP port 4370.
 * - Sends only the initial ZKTeco CONNECT packet.
 * - Does NOT read/write users, attendance records, time, or settings.
 *
 * Browser:
 * http://localhost/futoattend/tools/zkteco_test.php
 */
declare(strict_types=1);

function load_env(string $file): array {
    $env = [];
    if (is_file($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v, " \t\"'");
        }
    }
    return $env;
}

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function line(string $value): void {
    echo h($value) . "<br>\n";
}

function hexDump(string $data): string {
    if ($data === '') return '[empty]';
    return strtoupper(implode(' ', str_split(bin2hex($data), 2)));
}

function checksum(array $p): int {
    $l = count($p);
    $sum = 0;
    $i = $l;
    $j = 1;

    while ($i > 1) {
        $lo = $p['c' . $j] ?? 0;
        $hi = $p['c' . ($j + 1)] ?? 0;
        $sum += unpack('v', chr($lo) . chr($hi))[1];
        if ($sum > 65535) $sum -= 65535;
        $i -= 2;
        $j += 2;
    }

    if ($i) $sum += $p['c' . count($p)];
    while ($sum > 65535) $sum -= 65535;

    $sum = $sum > 0 ? -$sum : abs($sum);
    $sum -= 1;

    while ($sum < 0) $sum += 65535;
    return $sum;
}

function headerPacket(int $cmd, int $session, int $reply, string $payload = ''): string {
    $buf = pack('vvvv', $cmd, 0, $session, $reply) . $payload;
    $arr = unpack('C' . strlen($buf) . 'c', $buf);
    $sum = checksum($arr);

    $reply2 = $reply + 1;
    if ($reply2 >= 65535) $reply2 -= 65535;

    return pack('vvvv', $cmd, $sum, $session, $reply2) . $payload;
}

$env = load_env(__DIR__ . '/../.env');

$ip = $env['ZKTECO_IP'] ?? '';
$port = (int)($env['ZKTECO_PORT'] ?? 4370);
$deviceId = (int)($env['ZKTECO_DEVICE_ID'] ?? 1);

echo "<!doctype html>
<html>
<head>
<meta charset='utf-8'>
<title>ZKTeco TCP Diagnostic</title>
<style>
body{font-family:Arial,sans-serif;background:#f5f7fa;color:#172033;margin:0;padding:30px}
.box{max-width:800px;margin:auto;background:#fff;padding:28px;border-radius:14px;box-shadow:0 5px 20px rgba(0,0,0,.08)}
h2{margin-top:0}
pre{background:#111827;color:#e5e7eb;padding:16px;border-radius:8px;overflow:auto;white-space:pre-wrap;word-break:break-word}
.ok{color:#087f5b;font-weight:700}
.warn{color:#b45309;font-weight:700}
.fail{color:#c92a2a;font-weight:700}
</style>
</head>
<body><div class='box'>
<h2>ZKTeco TCP Diagnostic Test</h2>";

if ($ip === '') {
    echo "<p class='fail'>FAILED: ZKTECO_IP is missing from .env</p></div></body></html>";
    exit;
}

if (!extension_loaded('sockets')) {
    echo "<p class='fail'>FAILED: PHP sockets extension is not enabled.</p></div></body></html>";
    exit;
}

line("Device ID : {$deviceId}");
line("IP        : {$ip}");
line("TCP Port  : {$port}");
line("Test      : CONNECT packet only");
echo "<hr>";

const CMD_CONNECT = 1000;

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($sock === false) {
    echo "<p class='fail'>FAILED: Could not create TCP socket.</p>";
    echo "<pre>" . h(socket_strerror(socket_last_error())) . "</pre>";
    echo "</div></body></html>";
    exit;
}

try {
    line("1. Opening TCP connection...");

    if (!@socket_connect($sock, $ip, $port)) {
        $err = socket_last_error($sock);
        throw new RuntimeException(
            "TCP connection failed. Socket error {$err}: " . socket_strerror($err)
        );
    }

    echo "<p class='ok'>2. TCP connection established.</p>";

    /*
     * The existing project test uses the ZKTeco standalone packet format.
     * We send only CMD_CONNECT (1000), with no authentication or data command.
     */
    $packet = headerPacket(CMD_CONNECT, 0, 65534, '');

    line("3. Sending ZKTeco CONNECT packet...");
    line("Packet length: " . strlen($packet) . " bytes");
    line("Packet HEX: " . hexDump($packet));

    $sent = @socket_send($sock, $packet, strlen($packet), 0);

    if ($sent === false) {
        $err = socket_last_error($sock);
        throw new RuntimeException(
            "Send failed. Socket error {$err}: " . socket_strerror($err)
        );
    }

    line("Bytes sent: {$sent}");

    /*
     * Use select() so a lack of response is clearly reported as a timeout
     * rather than being confused with a Windows socket error message.
     */
    line("4. Waiting up to 5 seconds for device response...");

    $read = [$sock];
    $write = null;
    $except = null;

    $ready = @socket_select($read, $write, $except, 5);

    if ($ready === false) {
        $err = socket_last_error($sock);
        throw new RuntimeException(
            "socket_select failed. Socket error {$err}: " . socket_strerror($err)
        );
    }

    if ($ready === 0) {
        echo "<p class='warn'>5. TIMEOUT: The device did not send a TCP response within 5 seconds.</p>";
        echo "<p>This is the key diagnostic result. The TCP port is reachable, but the device did not answer the ZKTeco CONNECT packet.</p>";
    } else {
        $response = '';
        $n = @socket_recv($sock, $response, 8192, 0);

        if ($n === false) {
            $err = socket_last_error($sock);
            throw new RuntimeException(
                "Receive failed. Socket error {$err}: " . socket_strerror($err)
            );
        }

        if ($n === 0) {
            echo "<p class='warn'>5. DEVICE CLOSED THE TCP CONNECTION WITHOUT A RESPONSE.</p>";
        } else {
            echo "<p class='ok'>5. DEVICE SENT A RESPONSE.</p>";
            line("Response length: {$n} bytes");
            line("Response HEX:");
            echo "<pre>" . h(hexDump($response)) . "</pre>";

            if ($n >= 8) {
                $command = unpack('v', substr($response, 0, 2))[1];
                $checksumValue = unpack('v', substr($response, 2, 2))[1];
                $session = unpack('v', substr($response, 4, 2))[1];
                $reply = unpack('v', substr($response, 6, 2))[1];

                echo "<p><strong>Decoded header:</strong></p>";
                echo "<pre>";
                echo h("Command  : {$command}\n");
                echo h("Checksum : {$checksumValue}\n");
                echo h("Session  : {$session}\n");
                echo h("Reply ID : {$reply}");
                echo "</pre>";
            }
        }
    }

    echo "<hr>";
    echo "<p><strong>Important:</strong> This diagnostic does not authenticate and does not execute any attendance/user/settings command.</p>";

} catch (Throwable $e) {
    echo "<p class='fail'>FAILED: " . h($e->getMessage()) . "</p>";
} finally {
    @socket_close($sock);
}

echo "</div></body></html>";
?>
