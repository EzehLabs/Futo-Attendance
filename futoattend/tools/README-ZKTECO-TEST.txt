ZKTeco Proof-of-Concept Test

1. Copy futoattend into C:\xampp\htdocs\
2. Create futoattend\.env from .env.example and set ZKTECO_COMM_KEY to the key configured on the MB460 Plus.
3. Keep the laptop and MB460 Plus on the same Wi-Fi/hotspot.
4. Open Command Prompt in C:\xampp\htdocs\futoattend
5. Run: php tools\zkteco_test.php
6. Expected: CONNECT response 2005 (or 2000), then AUTH response 2000, followed by SUCCESS.

This test uses the standalone ZKTeco UDP protocol on port 4370 and does not change device users, attendance logs, time, or settings.
