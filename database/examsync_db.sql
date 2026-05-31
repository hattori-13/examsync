-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2026 at 04:54 PM
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
-- Database: `examsync_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `exam_subjects`
--

CREATE TABLE `exam_subjects` (
  `id` int(11) NOT NULL,
  `program` varchar(50) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `adviser` varchar(100) NOT NULL,
  `year_level` varchar(10) NOT NULL,
  `section` varchar(50) NOT NULL,
  `proctor` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_subjects`
--

INSERT INTO `exam_subjects` (`id`, `program`, `subject_code`, `adviser`, `year_level`, `section`, `proctor`, `uploaded_at`) VALUES
(1, 'BSIT', 'CC101', 'Eulalia S. Dagunan', '1', 'A', 'Jhon Mark M. Truces', '2026-05-30 03:16:13'),
(2, 'BSIT', 'CC101', 'Eulalia S. Dagunan', '1', 'B', 'Jade Mozunes', '2026-05-30 03:16:13'),
(3, 'BSIT', 'GE1', 'Mark Abad', '1', 'A', 'Carlos Castro', '2026-05-30 03:16:13'),
(4, 'BSIT', 'GE2', 'Mark Abad', '2', 'A', 'Diana Diaz', '2026-05-30 03:16:13'),
(5, 'BSIT', 'IT301', 'Ariel Solina', '3', 'A', 'Erwin Enriquez', '2026-05-30 03:16:13'),
(6, 'BSIT', 'NET1', 'Fiona Flores', '3', 'A', 'Fiona Flores', '2026-05-30 03:16:13'),
(7, 'BSIT', 'SAD1', 'George Garcia', '3', 'A', 'Hannah Hernandez', '2026-05-30 03:16:13'),
(8, 'BSIT', 'CAP401', 'Eulalia S. Dagunan', '4', 'A', 'Eulalia S. Dagunan', '2026-05-30 03:16:13'),
(9, 'BSA', 'ACCT1', 'Ian Ignacio', '1', 'A', 'Julia Javier', '2026-05-30 03:16:13'),
(10, 'BSA', 'ACCT1', 'Ian Ignacio', '1', 'B', 'Kevin King', '2026-05-30 03:16:13'),
(11, 'BSA', 'GE1', 'Mark Abad', '1', 'A', 'Laura Lopez', '2026-05-30 03:16:13'),
(12, 'BSA', 'GE2', 'Mark Abad', '2', 'A', 'Mike Mendoza', '2026-05-30 03:16:13'),
(13, 'BSA', 'ACCT2', 'Ian Ignacio', '2', 'A', 'Nina Navarro', '2026-05-30 03:16:13'),
(14, 'BSA', 'FIN1', 'Oscar Ortega', '3', 'A', 'Paula Perez', '2026-05-30 03:16:13'),
(15, 'BSA', 'FIN1', 'Oscar Ortega', '3', 'B', 'Oscar Ortega', '2026-05-30 03:16:13'),
(16, 'BSA', 'AUD1', 'Ian Ignacio', '4', 'A', 'Quinn Quizon', '2026-05-30 03:16:13'),
(17, 'BEED', 'EDUC1', 'Romy Reyes', '1', 'A', 'Romy Reyes', '2026-05-30 03:16:13'),
(18, 'BEED', 'GE1', 'Mark Abad', '1', 'A', 'Susan Santos', '2026-05-30 03:16:13'),
(19, 'BEED', 'GE3', 'Tom Torres', '1', 'A', 'Tom Torres', '2026-05-30 03:16:13'),
(20, 'BEED', 'EDUC2', 'Romy Reyes', '2', 'A', 'Uma Umali', '2026-05-30 03:16:13'),
(21, 'BEED', 'GE2', 'Mark Abad', '2', 'A', 'Victor Villanueva', '2026-05-30 03:16:13'),
(22, 'BEED', 'PRAC1', 'Wendy Wong', '4', 'A', 'Xavier Xavier', '2026-05-30 03:16:13'),
(23, 'BEED', 'PRAC1', 'Wendy Wong', '4', 'B', 'Wendy Wong', '2026-05-30 03:16:13'),
(24, 'BSED-ENG', 'ENG1', 'Yana Yabut', '1', 'A', 'Zack Zamora', '2026-05-30 03:16:13'),
(25, 'BSED-ENG', 'GE1', 'Mark Abad', '1', 'A', 'Jhon Mark M. Truces', '2026-05-30 03:16:13'),
(26, 'BSED-ENG', 'GE3', 'Tom Torres', '1', 'A', 'Jade Mozunes', '2026-05-30 03:16:13'),
(27, 'BSED-ENG', 'ENG2', 'Yana Yabut', '2', 'A', 'Ariel Solina', '2026-05-30 03:16:13'),
(28, 'BSED-ENG', 'GE2', 'Mark Abad', '2', 'A', 'Jobil Pandan', '2026-05-30 03:16:13'),
(29, 'BSED-ENG', 'LIT1', 'Yana Yabut', '3', 'A', 'Mark Abad', '2026-05-30 03:16:13'),
(30, 'BSED-ENG', 'PRAC1', 'Wendy Wong', '4', 'A', 'Carlos Castro', '2026-05-30 03:16:13'),
(31, 'BSED-SCI', 'SCI1', 'Diana Diaz', '1', 'A', 'Erwin Enriquez', '2026-05-30 03:16:13'),
(32, 'BSED-SCI', 'GE1', 'Mark Abad', '1', 'A', 'Fiona Flores', '2026-05-30 03:16:13'),
(33, 'BSED-SCI', 'GE3', 'Tom Torres', '1', 'A', 'George Garcia', '2026-05-30 03:16:13'),
(34, 'BSED-SCI', 'SCI2', 'Diana Diaz', '2', 'A', 'Hannah Hernandez', '2026-05-30 03:16:13'),
(35, 'BSED-SCI', 'GE2', 'Mark Abad', '2', 'A', 'Ian Ignacio', '2026-05-30 03:16:13'),
(36, 'BSED-SCI', 'BIO1', 'Diana Diaz', '3', 'A', 'Julia Javier', '2026-05-30 03:16:13'),
(37, 'BSED-SCI', 'PRAC1', 'Wendy Wong', '4', 'A', 'Kevin King', '2026-05-30 03:16:13'),
(38, 'BSBA-FM', 'BUS1', 'Laura Lopez', '1', 'A', 'Laura Lopez', '2026-05-30 03:16:13'),
(39, 'BSBA-FM', 'GE1', 'Mark Abad', '1', 'A', 'Mike Mendoza', '2026-05-30 03:16:13'),
(40, 'BSBA-FM', 'GE3', 'Tom Torres', '1', 'A', 'Nina Navarro', '2026-05-30 03:16:13'),
(41, 'BSBA-FM', 'FIN2', 'Oscar Ortega', '2', 'A', 'Paula Perez', '2026-05-30 03:16:13'),
(42, 'BSBA-FM', 'GE2', 'Mark Abad', '2', 'A', 'Quinn Quizon', '2026-05-30 03:16:13'),
(43, 'BSBA-FM', 'STRAT1', 'Laura Lopez', '4', 'A', 'Romy Reyes', '2026-05-30 03:16:13'),
(44, 'BSBA-HR', 'BUS1', 'Laura Lopez', '1', 'A', 'Susan Santos', '2026-05-30 03:16:13'),
(45, 'BSBA-HR', 'GE1', 'Mark Abad', '1', 'A', 'Tom Torres', '2026-05-30 03:16:13'),
(46, 'BSBA-HR', 'GE3', 'Tom Torres', '1', 'A', 'Uma Umali', '2026-05-30 03:16:13'),
(47, 'BSBA-HR', 'HRM1', 'Victor Villanueva', '2', 'A', 'Victor Villanueva', '2026-05-30 03:16:13'),
(48, 'BSBA-HR', 'GE2', 'Mark Abad', '2', 'A', 'Wendy Wong', '2026-05-30 03:16:13'),
(49, 'BSBA-HR', 'HRM2', 'Victor Villanueva', '3', 'A', 'Xavier Xavier', '2026-05-30 03:16:13'),
(50, 'BSBA-HR', 'STRAT1', 'Laura Lopez', '4', 'A', 'Yana Yabut', '2026-05-30 03:16:13'),
(51, 'BSIT', 'AP4', 'EULALIA DAGUNAN', '3', 'E', 'MONTON', '2026-05-30 03:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `generated_schedules`
--

CREATE TABLE `generated_schedules` (
  `id` int(11) NOT NULL,
  `exam_subject_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `time_slot_id` int(11) NOT NULL,
  `status` enum('Draft','Final') DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `generated_schedules`
--

INSERT INTO `generated_schedules` (`id`, `exam_subject_id`, `room_id`, `time_slot_id`, `status`, `created_at`) VALUES
(1, 3, 1, 1, 'Draft', '2026-05-30 03:31:49'),
(2, 2, 10, 1, 'Draft', '2026-05-30 03:31:49'),
(3, 43, 11, 1, 'Draft', '2026-05-30 03:31:49'),
(4, 34, 2, 1, 'Draft', '2026-05-30 03:31:49'),
(5, 45, 3, 1, 'Draft', '2026-05-30 03:31:49'),
(6, 23, 4, 1, 'Draft', '2026-05-30 03:31:49'),
(7, 6, 5, 1, 'Draft', '2026-05-30 03:31:49'),
(8, 30, 1, 2, 'Draft', '2026-05-30 03:31:49'),
(9, 18, 6, 1, 'Draft', '2026-05-30 03:31:49'),
(10, 4, 7, 1, 'Draft', '2026-05-30 03:31:49'),
(11, 50, 8, 1, 'Draft', '2026-05-30 03:31:49'),
(12, 12, 9, 1, 'Draft', '2026-05-30 03:31:49'),
(13, 9, 12, 1, 'Draft', '2026-05-30 03:31:49'),
(14, 7, 10, 2, 'Draft', '2026-05-30 03:31:49'),
(15, 27, 13, 1, 'Draft', '2026-05-30 03:31:49'),
(16, 16, 14, 1, 'Draft', '2026-05-30 03:31:49'),
(17, 1, 11, 2, 'Draft', '2026-05-30 03:31:49'),
(18, 24, 15, 1, 'Draft', '2026-05-30 03:31:49'),
(19, 46, 2, 2, 'Draft', '2026-05-30 03:31:49'),
(20, 42, 3, 2, 'Draft', '2026-05-30 03:31:49'),
(21, 38, 16, 1, 'Draft', '2026-05-30 03:31:49'),
(22, 48, 4, 2, 'Draft', '2026-05-30 03:31:49'),
(23, 21, 5, 2, 'Draft', '2026-05-30 03:31:49'),
(24, 14, 6, 2, 'Draft', '2026-05-30 03:31:49'),
(25, 32, 7, 2, 'Draft', '2026-05-30 03:31:49'),
(26, 13, 8, 2, 'Draft', '2026-05-30 03:31:49'),
(27, 5, 1, 3, 'Draft', '2026-05-30 03:31:49'),
(28, 47, 10, 3, 'Draft', '2026-05-30 03:31:49'),
(29, 29, 9, 2, 'Draft', '2026-05-30 03:31:49'),
(30, 39, 12, 2, 'Draft', '2026-05-30 03:31:49'),
(31, 36, 13, 2, 'Draft', '2026-05-30 03:31:49'),
(32, 35, 14, 2, 'Draft', '2026-05-30 03:31:49'),
(33, 25, 11, 3, 'Draft', '2026-05-30 03:31:49'),
(34, 41, 2, 3, 'Draft', '2026-05-30 03:31:49'),
(35, 28, 15, 2, 'Draft', '2026-05-30 03:31:49'),
(36, 51, 16, 2, 'Draft', '2026-05-30 03:31:49'),
(37, 49, 3, 3, 'Draft', '2026-05-30 03:31:49'),
(38, 19, 4, 3, 'Draft', '2026-05-30 03:31:49'),
(39, 26, 1, 4, 'Draft', '2026-05-30 03:31:49'),
(40, 44, 5, 3, 'Draft', '2026-05-30 03:31:49'),
(41, 11, 6, 3, 'Draft', '2026-05-30 03:31:49'),
(42, 15, 7, 3, 'Draft', '2026-05-30 03:31:49'),
(43, 22, 10, 4, 'Draft', '2026-05-30 03:31:49'),
(44, 40, 8, 3, 'Draft', '2026-05-30 03:31:49'),
(45, 20, 9, 3, 'Draft', '2026-05-30 03:31:49'),
(46, 10, 12, 3, 'Draft', '2026-05-30 03:31:49'),
(47, 8, 13, 3, 'Draft', '2026-05-30 03:31:49'),
(48, 33, 14, 3, 'Draft', '2026-05-30 03:31:49'),
(49, 37, 11, 4, 'Draft', '2026-05-30 03:31:49'),
(50, 31, 2, 4, 'Draft', '2026-05-30 03:31:49'),
(51, 17, 3, 4, 'Draft', '2026-05-30 03:31:49');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `created_at`) VALUES
(1, 'SC1', '2026-05-30 03:16:13'),
(2, 'SC2', '2026-05-30 03:16:13'),
(3, 'SC3', '2026-05-30 03:16:13'),
(4, 'SC4', '2026-05-30 03:16:13'),
(5, 'SC5', '2026-05-30 03:16:13'),
(6, 'SC6', '2026-05-30 03:16:13'),
(7, 'SC7', '2026-05-30 03:16:13'),
(8, 'SC8', '2026-05-30 03:16:13'),
(9, 'SC9', '2026-05-30 03:16:13'),
(10, 'SC10', '2026-05-30 03:16:13'),
(11, 'SC11', '2026-05-30 03:16:13'),
(12, 'SM1', '2026-05-30 03:16:13'),
(13, 'SM2', '2026-05-30 03:16:13'),
(14, 'SM3', '2026-05-30 03:16:13'),
(15, 'SM4', '2026-05-30 03:16:13'),
(16, 'SM5', '2026-05-30 03:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `day_name` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `day_name`, `start_time`, `end_time`, `created_at`) VALUES
(1, 'Day 1', '08:00:00', '09:00:00', '2026-05-30 03:16:13'),
(2, 'Day 1', '09:10:00', '10:10:00', '2026-05-30 03:16:13'),
(3, 'Day 1', '10:20:00', '11:20:00', '2026-05-30 03:16:13'),
(4, 'Day 1', '13:30:00', '14:30:00', '2026-05-30 03:16:13'),
(5, 'Day 1', '14:40:00', '15:40:00', '2026-05-30 03:16:13'),
(6, 'Day 1', '15:50:00', '16:50:00', '2026-05-30 03:16:13'),
(7, 'Day 2', '08:00:00', '09:00:00', '2026-05-30 03:16:13'),
(8, 'Day 2', '09:10:00', '10:10:00', '2026-05-30 03:16:13'),
(9, 'Day 2', '10:20:00', '11:20:00', '2026-05-30 03:16:13'),
(10, 'Day 2', '13:30:00', '14:30:00', '2026-05-30 03:16:13'),
(11, 'Day 2', '14:40:00', '15:40:00', '2026-05-30 03:16:13'),
(12, 'Day 2', '15:50:00', '16:50:00', '2026-05-30 03:16:13'),
(13, 'Day 3', '08:00:00', '09:00:00', '2026-05-30 03:16:13'),
(14, 'Day 3', '09:10:00', '10:10:00', '2026-05-30 03:16:13'),
(15, 'Day 3', '10:20:00', '11:20:00', '2026-05-30 03:16:13'),
(16, 'Day 3', '13:30:00', '14:30:00', '2026-05-30 03:16:13'),
(17, 'Day 3', '14:40:00', '15:40:00', '2026-05-30 03:16:13'),
(18, 'Day 3', '15:50:00', '16:50:00', '2026-05-30 03:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Registrar','Admin') DEFAULT 'Registrar',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'registrar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Registrar', '2026-05-30 02:45:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_subject` (`program`,`subject_code`,`year_level`,`section`),
  ADD KEY `idx_proctor` (`proctor`),
  ADD KEY `idx_subject_section` (`subject_code`,`section`);

--
-- Indexes for table `generated_schedules`
--
ALTER TABLE `generated_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_subject_id` (`exam_subject_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `time_slot_id` (`time_slot_id`),
  ADD KEY `idx_schedule_status` (`status`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_name` (`room_name`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_time_slot` (`day_name`,`start_time`,`end_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `generated_schedules`
--
ALTER TABLE `generated_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `generated_schedules`
--
ALTER TABLE `generated_schedules`
  ADD CONSTRAINT `generated_schedules_ibfk_1` FOREIGN KEY (`exam_subject_id`) REFERENCES `exam_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `generated_schedules_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `generated_schedules_ibfk_3` FOREIGN KEY (`time_slot_id`) REFERENCES `time_slots` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
