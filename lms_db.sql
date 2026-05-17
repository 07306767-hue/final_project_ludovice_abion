-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 07:48 AM
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
-- Database: `lms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `total_points` varchar(50) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `class_id`, `name`, `description`, `total_points`, `created_by`, `created_at`, `due_date`, `attachment_path`) VALUES
(1, 1, 'gagawa ng something', 'gawa kau ng sumething', '50', 12, '2026-04-19 01:13:45', NULL, NULL),
(2, 5, 'act 2', 'acnwer quiz 2', '10', 12, '2026-04-19 02:22:10', NULL, NULL),
(17, 8, 'dwada', 'pdwkadpad', '100', 18, '2026-05-11 10:08:07', '2222-02-20', NULL),
(18, 8, 'test files', 'dwioadwa', '100', 18, '2026-05-11 10:21:00', '2027-01-20', 'uploads/activities/activity_8_6a01ad8cdc8b29.06605628.docx'),
(19, 8, 'wkauhdiuwhad', 'dwahiudihwau', '100', 18, '2026-05-11 10:39:10', '2026-05-12', 'uploads/activities/activity_8_6a01b1ce100f21.74293608.jpg'),
(20, 8, 'test 2', 'test', '100', 18, '2026-05-17 05:05:54', '2026-10-10', 'uploads/activities/activity_8_6a094cb26e8271.53221465.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `class_id`, `faculty_id`, `title`, `content`, `created_at`) VALUES
(1, 8, 18, 'tatae ako', 'watch me do it', '2026-04-26 14:07:29'),
(2, 8, 18, 'sir', 'supot', '2026-05-13 05:25:12'),
(3, 8, 18, 'test', 'something test', '2026-05-17 05:06:21');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `classes_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `code` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `class_code` varchar(10) NOT NULL,
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`classes_id`, `subject`, `section`, `code`, `user_id`, `class_code`, `is_archived`) VALUES
(7, 'java', 'B', '', 18, 'B88C8B', 0),
(8, 'python', 'A', '', 18, '043112', 0),
(10, 'C++', 'F', '', 18, '7F2BE2', 0),
(11, 'javascript', 'F', '', 18, '9B85B0', 0);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `user_id`, `class_id`) VALUES
(5, 20, 7),
(6, 19, 8),
(9, 19, 7);

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `grade` varchar(50) DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `activity_id`, `student_id`, `answer`, `attachment_path`, `submitted_at`, `grade`, `graded_at`, `graded_by`) VALUES
(1, 1, 13, 'something', NULL, '2026-04-19 01:23:56', '40', '2026-04-19 01:24:23', 12),
(2, 4, 13, 'something', NULL, '2026-04-19 03:19:36', NULL, NULL, NULL),
(3, 5, 13, 'dwakdhgwa', NULL, '2026-04-19 03:22:32', NULL, NULL, NULL),
(4, 6, 13, 'nc', NULL, '2026-04-19 03:30:32', NULL, NULL, NULL),
(6, 8, 20, '10', NULL, '2026-04-25 11:13:33', NULL, NULL, NULL),
(7, 7, 20, '100', NULL, '2026-04-25 11:13:37', NULL, NULL, NULL),
(14, 18, 19, 'kwdka', 'uploads/submissions/submission_18_19_6a01af98d11146.28532487.jpg', '2026-05-11 10:29:37', '100', '2026-05-17 05:22:18', 18),
(16, 20, 19, 'okiii', NULL, '2026-05-17 05:06:46', '90', '2026-05-17 05:22:00', 18);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','faculty') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `password`, `role`) VALUES
(18, 'justin', 'Ludovice', 'justin@gmail.com', '$2y$10$ZRFZVadGu/C2RX1lpBsBuunaQ43ABEO6RP0X8AKguV3idxZe.QEY.', 'faculty'),
(19, 'abion', 'cybell', 'abion@gmail.com', '$2y$10$ytoSY7rglbcwKgRT7Ta7vukWtfwU.R1syCCmXmTMBIPE3kYcj5Fz.', 'student'),
(20, 'ace', 'valla', 'ace@gmail.com', '$2y$10$HsF51WFVgZrlOoSvS5Ey1uaerPNfZvcStU.AxGIOju4zVtqjJYGXe', 'student'),
(21, 'justin', 'Ludovice', 'prof@gmail.com', '$2y$10$GpOjZt1hoPWnPyqOuovhUufKYn.dKulhpUic8wVdjn5u.kSfRPhca', 'student'),
(22, 'jasmine', 'ludovice', 'jas@gmail.com', '$2y$10$qyTK1YUWL0xZ6ShyRAQGau.waj4XPe8j9GDf2LaynmPfapz3EXOAm', 'student'),
(24, 'test', 'subject', 'test@gmail.com', '$2y$10$9dFvwOZQq8emVpOvR9nZN.3Fm8uE1ptF1T8XRaFHqGpevoEv.eMLe', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`classes_id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `fk_enroll_user` (`user_id`),
  ADD KEY `fk_enroll_class` (`class_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `classes_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`classes_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`classes_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enroll_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
