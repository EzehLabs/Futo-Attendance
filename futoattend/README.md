# FUTO Attendance MVP v4 — Functional PHP/MySQL MVP

This version keeps the project intentionally simple: PHP pages + PHP API + MySQL + plain JavaScript/CSS.

## Setup in XAMPP

1. Copy the `futoattend` folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open phpMyAdmin: `http://localhost/phpmyadmin/`
4. Import `database/schema.sql`.
5. Import `database/seed.sql`.
6. Open: `http://localhost/futoattend/public/`

For an existing installation, keep your database and run the required migrations in order. For a fresh local test, importing the schema and seed files is enough; the seed already includes five demo student email addresses.

## Run with Docker

Requirements: Docker Desktop (or another Docker Engine) with Docker Compose.

From the directory containing this `README.md` and `docker-compose.yml`, run:

```sh
docker compose up --build
```

If your terminal is in the parent folder (`C:\Users\USER\Downloads\futoattend`), run:

```powershell
docker compose -f .\futoattend\docker-compose.yml up --build
```

Open `http://localhost:8000`. The first start creates a MySQL database, imports the schema and demo seed data, and persists database files in the `mysql_data` volume. Stop the app with `Ctrl+C`; use `docker compose down` to stop and remove its containers. The database volume is preserved, so later starts keep their data. To start with a fresh database, first stop the stack and then remove the volume with `docker compose down --volumes`.

The Compose setup binds the web app to localhost only and uses development-only database credentials. Set `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`, and `SYNC_API_KEY` in your shell or a local Compose `.env` file to override them. Compose passes only these selected settings to the containers; it does not load the app's `.env` file or expose its mail/device credentials.

## Demo accounts

All demo passwords are `password`.

- Admin: `ADMIN001`
- Lecturer: `STAFF/CSC/001`
- Student: `1`

## Simulate attendance without ZKTeco

Use the Admin account for setup, then the Lecturer account for scheduling.

1. **Venues** — confirm `LT 2` exists.
2. **ZKTeco Devices** — confirm `ZK001` exists.
3. **Courses** — confirm the demo courses exist and are assigned to `STAFF/CSC/001`.
4. **Enrolments** — confirm the student is enrolled in the course.
5. Log in as `STAFF/CSC/001`, open **Class Sessions**, and create a session for the course, venue, device, date and time.
6. **Test Attendance** (Admin) — choose `ZK001`, choose the student's matric number, select a punch time inside the session, and click **Send Punch**.
7. You should get **Present** when the punch is within the configured late window (15 minutes by default) and **Late** afterwards.
8. Open **Attendance** to see the record. Log in as the student to see their attendance.
9. After the scheduled end time, the session is closed automatically when the cron script runs, or when the API next receives a request. Enrolled students without an attendance record are marked Absent. A lecturer can also close an ended session from **Class Sessions**.

## Test manual correction

Admin or the assigned lecturer can open Attendance and click **Correct**. Enter a new status and a reason. The attendance record is changed to `MANUAL` and an audit event is created.

## Real ZKTeco integration later

The real device uses:

`POST /public/api.php?route=device/punch`

with header:

`X-Sync-Key: <your SYNC_API_KEY>`

and JSON such as:

```json
{
  "device_id": 1,
  "device_user_id": "1",
  "punch_time": "2026-10-02 10:07:00",
  "device_record_key": "device-specific-unique-record-id"
}
```

The device must be registered to a venue and the student's device User ID must match the student's login/matric ID.

Do not expose the ZKTeco communication port directly to the public internet. ZKTeco documents that supported standalone push devices can communicate through its web-based ZKBio Time ecosystem; exact device settings depend on the model/firmware. Official page: https://www.zkteco.com/en/ZKBioTime/ZKBioTime

## What is intentionally still outside this MVP

- First-login forced password change
- Email password reset
- Recurring weekly timetable generator
- PDF reports
- Automatic background sync scheduler/ADMS connector for a specific ZKTeco model
- Production backups/restore automation
- Full production rate limiting, deployment/HTTPS configuration and operational monitoring

Those are next-stage/production items; the core demo workflow is now functional.


## MVP v7 update
- Courses now have a configurable planned class count; default is 17 sessions per semester.
- Student attendance percentage and eligibility use the planned session count, so one attended class is not automatically 100%.
- Admin can change a course's planned class count from Courses → Set Classes.
- Session creation and manual closure are lecturer-only; administrators no longer create or close sessions.
- If upgrading an existing v6 database, run `database/migration_v7.sql` once in phpMyAdmin.

## Added features (v8)
- Student and lecturer bulk registration via CSV files exported from Excel. Required headers: `login_id`, `first_name`, `last_name`; optional headers: `email`, `phone`, `department`, `level`, `password`.
- Bulk course enrolment by selecting multiple students.
- Lecturer dashboard session creation and closing for assigned courses, plus a per-course student list and attendance percentage.
- Session creation emails enrolled students who have valid email addresses. Configure `MAIL_FROM` in `.env` and ensure PHP `mail()` is configured on the hosting server; `emails_sent` reports accepted sends, not inbox delivery.
- Automatic closure: configure a server cron job to run `php /path/to/futoattend/tools/close_finished_sessions.php` every minute. API requests also close overdue sessions as a fallback. The script finalizes absent records before marking a session closed.
- The attendance table's unique key on `(session_id, student_id)` and `INSERT IGNORE` processing prevent duplicate attendance records for repeated punches in the same class.
- Run `database/migration_v8.sql` once on an existing installation to backfill email addresses for the five demo students. This migration assumes the `users` table already exists.


## Email notifications (localhost and production)

The app sends email notifications when a lecturer creates a session or cancels an upcoming session. SMTP is used when `SMTP_HOST` is configured. If SMTP is not configured, PHP `mail()` is used as a fallback; on a typical XAMPP Windows install that fallback is not configured by default.

### Fast local test with Mailpit (recommended)

1. Install and start Mailpit from https://github.com/axllent/mailpit (Windows users can download the Windows release; Docker users can run `docker run --rm -p 1025:1025 -p 8025:8025 axllent/mailpit`).
2. Keep the local `.env` email settings as `SMTP_HOST=127.0.0.1`, `SMTP_PORT=1025`, `SMTP_SECURE=none`, with blank `SMTP_USER` and `SMTP_PASS`. Set `MAIL_FROM` to a simple address such as `no-reply@futoattend.local` if you prefer.
3. Start/restart Apache. Open the Mailpit inbox at http://localhost:8025. Mailpit captures messages locally; it does not deliver them to real student inboxes.
4. Log in as a lecturer, create a session for a course with enrolled students whose `users.email` values are valid, and confirm each message appears in Mailpit. Then cancel a future session and confirm cancellation messages appear. The API response/toast reports accepted SMTP sends, not final inbox delivery.
5. For real external inbox delivery, use your provider's SMTP host/port/security and credentials in `.env`. For Gmail, use an App Password (not your normal account password) and typically port 587 with `SMTP_SECURE=tls`; do not commit or share SMTP credentials.

The `.env` file contains secrets and should not be committed or exposed on a public server. Before hosting, change the sender to an address you control, use provider-approved SMTP credentials, and configure environment-specific settings.
