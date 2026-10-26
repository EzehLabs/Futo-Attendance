USE futo_attendance;
-- Email is already part of users in schema.sql. These values ensure the five demo student accounts are email-ready.
UPDATE users SET email='chinedu.okafor@futo.edu.ng' WHERE login_id='1' AND (email IS NULL OR email='');
UPDATE users SET email='ada.eze@futo.edu.ng' WHERE login_id='FUTO/CSC/2026/002' AND (email IS NULL OR email='');
UPDATE users SET email='john.okoro@futo.edu.ng' WHERE login_id='FUTO/CSC/2026/003' AND (email IS NULL OR email='');
UPDATE users SET email='grace.nwachukwu@futo.edu.ng' WHERE login_id='FUTO/CSC/2026/004' AND (email IS NULL OR email='');
UPDATE users SET email='michael.ibe@futo.edu.ng' WHERE login_id='FUTO/CSC/2026/005' AND (email IS NULL OR email='');
