-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2026 at 04:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `futo_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('Present','Late','Absent','Excused') NOT NULL,
  `punch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source` enum('DEVICE','SYSTEM','MANUAL') NOT NULL DEFAULT 'DEVICE',
  `correction_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `session_id`, `student_id`, `status`, `punch_id`, `source`, `correction_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'Present', 1, 'DEVICE', NULL, '2026-10-14 14:00:29', '2026-10-14 14:00:29'),
(2, 1, 3, 'Present', 2, 'DEVICE', NULL, '2026-10-14 14:01:00', '2026-10-14 14:01:00'),
(3, 1, 5, 'Present', 3, 'DEVICE', NULL, '2026-10-14 14:01:10', '2026-10-14 14:01:10'),
(4, 1, 6, 'Late', 4, 'DEVICE', NULL, '2026-10-14 14:05:35', '2026-10-14 14:05:35');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_id`, `action`, `entity`, `entity_id`, `details`, `created_at`) VALUES
(1, 1, 'LOGIN', 'user', 1, NULL, '2026-10-14 13:07:56'),
(2, 3, 'LOGIN', 'user', 3, NULL, '2026-10-14 13:16:02'),
(3, 1, 'LOGIN', 'user', 1, NULL, '2026-10-14 13:16:16'),
(4, 1, 'ASSIGN', 'course', 1, 'Lecturer 2', '2026-10-14 13:17:01'),
(5, 1, 'ASSIGN', 'course', 2, 'Lecturer 2', '2026-10-14 13:17:11'),
(6, 1, 'ASSIGN', 'course', 3, 'Lecturer 2', '2026-10-14 13:17:20'),
(7, 1, 'ASSIGN', 'course', 4, 'Lecturer 2', '2026-10-14 13:17:29'),
(8, 1, 'ASSIGN', 'course', 5, 'Lecturer 2', '2026-10-14 13:17:35'),
(9, 1, 'ASSIGN', 'course', 6, 'Lecturer 2', '2026-10-14 13:17:43'),
(10, 1, 'BULK_CREATE', 'users', NULL, 'Role student; created 3', '2026-10-14 13:40:43'),
(11, 1, 'BULK_ENROL', 'enrolment', NULL, 'Course 1; added 4', '2026-10-14 13:42:17'),
(12, 1, 'BULK_ENROL', 'enrolment', NULL, 'Course 2; added 4', '2026-10-14 13:42:44'),
(13, 1, 'BULK_ENROL', 'enrolment', NULL, 'Course 3; added 4', '2026-10-14 13:43:06'),
(14, 1, 'BULK_ENROL', 'enrolment', NULL, 'Course 4; added 4', '2026-10-14 13:43:17'),
(15, 1, 'BULK_ENROL', 'enrolment', NULL, 'Course 5; added 4', '2026-10-14 13:43:34'),
(16, 1, 'BULK_ENROL', 'enrolment', NULL, 'Course 6; added 4', '2026-10-14 13:43:49'),
(17, 3, 'LOGIN', 'user', 3, NULL, '2026-10-14 13:44:56'),
(18, 2, 'LOGIN', 'user', 2, NULL, '2026-10-14 13:45:04'),
(19, 1, 'LOGIN', 'user', 1, NULL, '2026-10-14 13:50:38'),
(20, 2, 'LOGIN', 'user', 2, NULL, '2026-10-14 13:54:34'),
(21, 2, 'CREATE', 'session', 1, 'Lecturer created; notification queued for background request', '2026-10-14 13:55:37'),
(22, 2, 'CREATE_NOTIFY', 'session', 1, 'Notification emails successfully sent: 4', '2026-10-14 13:55:49'),
(23, 3, 'LOGIN', 'user', 3, NULL, '2026-10-14 13:58:21'),
(24, 4, 'LOGIN', 'user', 4, NULL, '2026-10-14 13:59:30'),
(25, 6, 'LOGIN', 'user', 6, NULL, '2026-10-14 14:05:27'),
(26, 2, 'LOGIN', 'user', 2, NULL, '2026-10-14 14:05:55'),
(27, 1, 'LOGIN', 'user', 1, NULL, '2026-10-14 14:07:08'),
(28, 6, 'LOGIN', 'user', 6, NULL, '2026-10-14 14:08:21'),
(29, 1, 'LOGIN', 'user', 1, NULL, '2026-10-14 14:08:30');

-- --------------------------------------------------------

--
-- Table structure for table `class_sessions`
--

CREATE TABLE `class_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `venue_id` bigint(20) UNSIGNED NOT NULL,
  `device_id` bigint(20) UNSIGNED NOT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `closed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_sessions`
--

INSERT INTO `class_sessions` (`id`, `course_id`, `venue_id`, `device_id`, `session_date`, `start_time`, `end_time`, `cancelled`, `closed`) VALUES
(1, 1, 4, 1, '2026-10-14', '15:00:00', '15:15:00', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `title` varchar(200) NOT NULL,
  `department` varchar(150) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `planned_sessions` int(10) UNSIGNED NOT NULL DEFAULT 17,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `title`, `department`, `semester`, `planned_sessions`, `active`, `created_at`) VALUES
(1, 'IFT 401', 'Artificial Intelligence', 'Information Technology', 'First Semester', 10, 1, '2026-10-03 04:54:15'),
(2, 'IFT 403', 'Software Engineering', 'Information Technology', 'First Semester', 10, 1, '2026-10-03 04:54:15'),
(3, 'IFT 405', 'Computer Networks', 'Information Technology ', 'First Semester', 10, 1, '2026-10-03 04:54:15'),
(4, 'IFT 407', 'Database Systems', 'Information Technology', 'First Semester', 10, 1, '2026-10-03 04:54:15'),
(5, 'IFT 409', 'Web Application Development', 'Information Technology', 'First Semester', 10, 1, '2026-10-03 04:54:15'),
(6, 'IFT 410', 'Web Development', 'Information Technology', 'First Semester', 10, 1, '2026-10-03 05:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `course_lecturers`
--

CREATE TABLE `course_lecturers` (
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `lecturer_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_lecturers`
--

INSERT INTO `course_lecturers` (`course_id`, `lecturer_id`) VALUES
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2);

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `device_code` varchar(80) NOT NULL,
  `name` varchar(150) NOT NULL,
  `venue_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `port` int(11) NOT NULL DEFAULT 4370,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `device_code`, `name`, `venue_id`, `ip_address`, `port`, `active`, `last_seen_at`) VALUES
(1, 'TTQ5254800322', 'ZKTeco MB4-60 Plus', 1, '10.180.84.136', 4370, 1, '2026-10-14 15:05:35'),
(4, 'ZK002', 'ZKTeco simulation', 4, '192.168.1.60', 4370, 1, '2026-10-13 03:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `enrolments`
--

CREATE TABLE `enrolments` (
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrolments`
--

INSERT INTO `enrolments` (`course_id`, `student_id`) VALUES
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(5, 3),
(5, 4),
(5, 5),
(5, 6),
(6, 3),
(6, 4),
(6, 5),
(6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `raw_punches`
--

CREATE TABLE `raw_punches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `device_id` bigint(20) UNSIGNED NOT NULL,
  `device_user_id` varchar(100) NOT NULL,
  `punch_time` datetime NOT NULL,
  `device_record_key` varchar(190) NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `raw_punches`
--

INSERT INTO `raw_punches` (`id`, `device_id`, `device_user_id`, `punch_time`, `device_record_key`, `received_at`) VALUES
(1, 1, '2', '2026-10-14 15:00:28', '0f311d4b58f71695b0e17e554810ee05332a614e1c2bfb8e2f6c73fe04b791c8', '2026-10-14 14:00:29'),
(2, 1, '1', '2026-10-14 15:00:59', '464b8615a6d815f62ee3abbcce5d258b3de4d1e5723c9761de7b3dd74f603bd5', '2026-10-14 14:01:00'),
(3, 1, '3', '2026-10-14 15:01:09', 'd95fd7de1cfbf007ce91a4a63c01ddd50a19ae282c797cd025f98deabf45b7cf', '2026-10-14 14:01:10'),
(4, 1, '4', '2026-10-14 15:05:35', 'f727f73b15374bf1514e0d8951561d24f68c25b6da5e847f37091f7ed32e102d', '2026-10-14 14:05:35');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('current_semester', '2026/2027 First Semester'),
('eligibility_threshold', '70'),
('late_window_minutes', '5');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `login_id` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','lecturer','admin') NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `level` varchar(30) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login_id`, `password_hash`, `role`, `first_name`, `last_name`, `email`, `phone`, `department`, `level`, `active`, `created_at`) VALUES
(1, 'ADMIN001', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'admin', 'Admin', 'User', 'admin@futo.edu.ng', '08000000000', 'Authority', '400', 1, '2026-10-14 13:07:05'),
(2, 'STAFF/CSC/001', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'lecturer', 'Emeka', 'Nwosu', 'emeka.nwosu@futo.edu.ng', '08000000001', 'Computer Science', NULL, 1, '2026-10-14 13:07:05'),
(3, '1', '$2y$12$gOh3lnSTov9XuDjCOjnLMuWl0iyNGzDjgnubaU8DVgJ8el1ETp2r6', 'student', 'Ikechukwu', 'Emmanuel', 'eikechukwu903@gmail.com', '08061698399', 'Information Technology', '400', 1, '2026-10-14 13:15:41'),
(4, '2', '$2y$10$.JLYCEyEvjRz1vqdYdM4duaKuua7GYrlUsgFBHWoA4/xtcGxkl.r2', 'student', 'Christian', 'Obinna', 'chrisboy385@gmail.com', '7025396922', 'Information Technology', '400', 1, '2026-10-14 13:40:43'),
(5, '3', '$2y$10$QPPUZFG0sZucy4pGWOOPCuX42Mh0ZUiiXYf95Y1alF8Xgouc0.yj2', 'student', 'Smart', 'Gabriel', 'gabirel1leonard@gmail.com', '8077769879', 'Information Technology', '400', 1, '2026-10-14 13:40:43'),
(6, '4', '$2y$10$JMOBCGX4H6Mg2GToSlLktucmCQEqsTvdK.dyPBIwSavEoLyY4C7lC', 'student', 'omenaoba', 'kosi', 'joechris042@gmail.com', '8141596631', 'Information Technology', '400', 1, '2026-10-14 13:40:43');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`) VALUES
(1, 'IFT Hardware Lab'),
(3, 'IFT Lecture Hall 1'),
(2, 'IFT Lecture Hall 2'),
(5, 'IFT Software Lab'),
(4, 'SMAT AUD');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `one_student_session` (`session_id`,`student_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `punch_id` (`punch_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actor_id` (`actor_id`);

--
-- Indexes for table `class_sessions`
--
ALTER TABLE `class_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `session_date` (`session_date`,`start_time`,`end_time`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `course_lecturers`
--
ALTER TABLE `course_lecturers`
  ADD PRIMARY KEY (`course_id`,`lecturer_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_code` (`device_code`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `enrolments`
--
ALTER TABLE `enrolments`
  ADD PRIMARY KEY (`course_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `raw_punches`
--
ALTER TABLE `raw_punches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_record_key` (`device_record_key`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `device_user_id` (`device_user_id`,`punch_time`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_id` (`login_id`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `class_sessions`
--
ALTER TABLE `class_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `raw_punches`
--
ALTER TABLE `raw_punches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `class_sessions` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`punch_id`) REFERENCES `raw_punches` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `class_sessions`
--
ALTER TABLE `class_sessions`
  ADD CONSTRAINT `class_sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `class_sessions_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`),
  ADD CONSTRAINT `class_sessions_ibfk_3` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`);

--
-- Constraints for table `course_lecturers`
--
ALTER TABLE `course_lecturers`
  ADD CONSTRAINT `course_lecturers_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `course_lecturers_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`);

--
-- Constraints for table `enrolments`
--
ALTER TABLE `enrolments`
  ADD CONSTRAINT `enrolments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `enrolments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `raw_punches`
--
ALTER TABLE `raw_punches`
  ADD CONSTRAINT `raw_punches_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
