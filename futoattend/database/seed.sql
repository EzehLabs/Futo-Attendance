USE futo_attendance;

INSERT INTO users(login_id,password_hash,role,first_name,last_name,email,phone,department,level) VALUES
('ADMIN001', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'admin','Admin','User','admin@futo.edu.ng','08000000000','Computer Science',NULL),
('STAFF/CSC/001', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'lecturer','Emeka','Nwosu','emeka.nwosu@futo.edu.ng','08000000001','Computer Science',NULL),
('STAFF/CSC/002', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'lecturer','Ngozi','Okafor','ngozi.okafor@futo.edu.ng','08000000002','Computer Science',NULL),
('STAFF/CSC/003', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'lecturer','Chinedu','Eze','chinedu.eze@futo.edu.ng','08000000003','Computer Science',NULL),
('STAFF/CSC/004', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'lecturer','Amaka','Okoro','amaka.okoro@futo.edu.ng','08000000004','Computer Science',NULL),
('1', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'student','Chinedu','Okafor','chinedu.okafor@futo.edu.ng','08000000011','Computer Science','400'),
('FUTO/CSC/2026/002', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'student','Ada','Eze','ada.eze@futo.edu.ng','08000000012','Computer Science','400'),
('FUTO/CSC/2026/003', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'student','John','Okoro','john.okoro@futo.edu.ng','08000000013','Computer Science','400'),
('FUTO/CSC/2026/004', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'student','Grace','Nwachukwu','grace.nwachukwu@futo.edu.ng','08000000014','Computer Science','400'),
('FUTO/CSC/2026/005', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'student','Michael','Ibe','michael.ibe@futo.edu.ng','08000000015','Computer Science','400');

INSERT INTO courses(code,title,department,semester,planned_sessions) VALUES
('CSC 401','Artificial Intelligence','Computer Science','First Semester'),
('CSC 403','Software Engineering','Computer Science','First Semester'),
('CSC 405','Computer Networks','Computer Science','First Semester'),
('CSC 407','Database Systems','Computer Science','First Semester'),
('CSC 409','Web Application Development','Computer Science','First Semester');

INSERT INTO course_lecturers(course_id,lecturer_id)
SELECT c.id,u.id FROM courses c JOIN users u ON u.login_id='STAFF/CSC/001' WHERE c.code IN ('CSC 401','CSC 405');
INSERT INTO course_lecturers(course_id,lecturer_id)
SELECT c.id,u.id FROM courses c JOIN users u ON u.login_id='STAFF/CSC/002' WHERE c.code='CSC 403';
INSERT INTO course_lecturers(course_id,lecturer_id)
SELECT c.id,u.id FROM courses c JOIN users u ON u.login_id='STAFF/CSC/003' WHERE c.code='CSC 407';
INSERT INTO course_lecturers(course_id,lecturer_id)
SELECT c.id,u.id FROM courses c JOIN users u ON u.login_id='STAFF/CSC/004' WHERE c.code='CSC 409';

INSERT INTO enrolments(course_id,student_id)
SELECT c.id,u.id FROM courses c CROSS JOIN users u WHERE u.role='student';

INSERT INTO venues(name) VALUES ('LT 2'),('ICT Lecture Hall'),('Computer Lab 1');
INSERT INTO devices(device_code,name,venue_id,ip_address)
SELECT 'ZK001','ZKTeco Main Device',id,'192.168.1.50' FROM venues WHERE name='LT 2';

INSERT INTO settings(setting_key,setting_value) VALUES
('late_window_minutes','15'),('eligibility_threshold','75'),('current_semester','2026/2027 First Semester');
