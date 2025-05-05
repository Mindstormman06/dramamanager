-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2025 at 10:57 AM
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
-- Database: `qssdrama79`
--

-- --------------------------------------------------------

--
-- Table structure for table `characters`
--

CREATE TABLE `characters` (
  `id` int(11) NOT NULL,
  `stage_name` varchar(100) NOT NULL,
  `real_name` varchar(100) DEFAULT NULL,
  `show_id` int(11) DEFAULT NULL,
  `mention_count` int(11) DEFAULT 0,
  `line_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `characters`
--

INSERT INTO `characters` (`id`, `stage_name`, `real_name`, `show_id`, `mention_count`, `line_count`) VALUES
(195, 'Chelsi', '1', 24, 17, 13),
(196, 'Sagina', '2', 24, 20, 14),
(197, 'Blaire', '3', 24, 36, 18),
(198, 'Ethan', '4', 24, 35, 27),
(199, 'Paris', '5', 24, 18, 7),
(200, 'James', '6', 24, 72, 33),
(201, 'Zach', NULL, 24, 42, 25),
(202, 'Jules', NULL, 24, 48, 27),
(203, 'Corbyn', NULL, 24, 14, 8),
(204, 'Adrian', NULL, 24, 12, 9),
(205, 'Daisy', NULL, 24, 12, 8),
(206, 'Sid', NULL, 24, 26, 9),
(207, 'Orfhlaith', NULL, 24, 19, 15),
(208, 'Simon', NULL, 24, 3, 1),
(209, 'Mom', NULL, 24, 4, 2),
(210, 'Little James', NULL, 24, 2, 2),
(211, 'Dad', NULL, 24, 3, 2),
(212, 'Student 1', NULL, 24, 6, 1),
(214, 'test', NULL, 24, 0, 0),
(215, 'test2', 'Caiomhe Carroll', 24, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `costumecategories`
--

CREATE TABLE `costumecategories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `costumecategories`
--

INSERT INTO `costumecategories` (`id`, `name`) VALUES
(2, 'd'),
(1, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `costumes`
--

CREATE TABLE `costumes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `decade` varchar(20) DEFAULT NULL,
  `style` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `itemcondition` varchar(20) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `costumes`
--

INSERT INTO `costumes` (`id`, `name`, `photo_url`, `decade`, `style`, `location`, `itemcondition`, `category_id`) VALUES
(11, 'test', NULL, 'Ancient', 'Casual', 'Rack A-1', 'A', 1),
(12, 'test1', NULL, '1990s', 'Formal', 'Shelf B-2', 'R', 2);

-- --------------------------------------------------------

--
-- Table structure for table `ideas`
--

CREATE TABLE `ideas` (
  `id` int(11) NOT NULL,
  `quote` text NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `propcategories`
--

CREATE TABLE `propcategories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `propcategories`
--

INSERT INTO `propcategories` (`id`, `name`) VALUES
(2, 'Bathroom'),
(1, 'Kitchen'),
(3, 'Test');

-- --------------------------------------------------------

--
-- Table structure for table `props`
--

CREATE TABLE `props` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `itemcondition` char(1) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `props`
--

INSERT INTO `props` (`id`, `name`, `description`, `location`, `itemcondition`, `category_id`, `photo_url`) VALUES
(6, 'test1', NULL, 'Shelf B-2', 'B', 1, NULL),
(7, 'test', NULL, 'Rack A-1', 'D', 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `showcostumes`
--

CREATE TABLE `showcostumes` (
  `show_id` int(11) NOT NULL,
  `costume_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `showcostumes`
--

INSERT INTO `showcostumes` (`show_id`, `costume_id`) VALUES
(24, 11),
(24, 12);

-- --------------------------------------------------------

--
-- Table structure for table `showlines`
--

CREATE TABLE `showlines` (
  `id` int(11) NOT NULL,
  `character_id` int(11) DEFAULT NULL,
  `show_id` int(11) DEFAULT NULL,
  `line_number` int(11) DEFAULT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `showprops`
--

CREATE TABLE `showprops` (
  `show_id` int(11) NOT NULL,
  `prop_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `showprops`
--

INSERT INTO `showprops` (`show_id`, `prop_id`) VALUES
(24, 7);

-- --------------------------------------------------------

--
-- Table structure for table `shows`
--

CREATE TABLE `shows` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `script_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shows`
--

INSERT INTO `shows` (`id`, `title`, `year`, `semester`, `notes`, `script_path`) VALUES
(24, 'Robin High Ep2', 2025, '2', '', '../uploads/script_6817104c14d400.67199926.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `studentcharacters`
--

CREATE TABLE `studentcharacters` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `studentcharacters`
--

INSERT INTO `studentcharacters` (`id`, `character_id`, `student_id`, `created_at`) VALUES
(1, 214, 5, '2025-05-04 08:47:13'),
(2, 215, 5, '2025-05-04 08:49:39');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `username`, `first_name`, `last_name`, `teacher_id`, `created_at`) VALUES
(5, 'ccarroll', 'Caiomhe', 'Carroll', 3, '2025-05-04 07:34:26');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `preferred_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `district_no` varchar(10) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `teacher_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `username`, `first_name`, `last_name`, `preferred_name`, `email`, `district_no`, `password_hash`, `teacher_code`, `created_at`) VALUES
(3, 'rjackson79', 'Rosanna', 'Jackson', 'Ms. Jackson', 'rjackson@sd79.bc.ca', '79', '$2y$10$Y97jfv5jcBzW6euBpmtCbOWDg2ZZ/yaeD5BBc3VeeE4/1MlLH.CZy', 'JACRO79', '2025-05-04 07:19:43'),
(4, 'ntrickett79', 'Nolan', 'Trickett', 'Mr Trickett', 'ntrickett@sd79.bc.ca', '79', '$2y$10$5kkrLllcSE494F9xKi7B0Ot6pPOBvUQgnEHRDQNiFC2whSkb4/dm.', 'TRINO79', '2025-05-04 07:52:29');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_links`
--

CREATE TABLE `teacher_links` (
  `id` int(11) NOT NULL,
  `lead_teacher_id` int(11) NOT NULL,
  `linked_teacher_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_links`
--

INSERT INTO `teacher_links` (`id`, `lead_teacher_id`, `linked_teacher_id`, `created_at`) VALUES
(4, 3, 4, '2025-05-04 07:59:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','teacher','indexer','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `role`, `created_at`, `remember_token`) VALUES
(1, 'Aiden', '$2y$10$AWzt.yih8IAdKBEb.aZJvOzsQMh/ShfSUCeuuk6mrpvk6KQMFntIm', 'aidenadzich@gmail.com', 'admin', '2025-05-04 03:30:28', NULL),
(2, 'rjackson79', '$2y$10$Y97jfv5jcBzW6euBpmtCbOWDg2ZZ/yaeD5BBc3VeeE4/1MlLH.CZy', 'rjackson@sd79.bc.ca', 'teacher', '2025-05-04 07:19:43', 'd8321c2e999606c1e4464859e4bc3f47853448ccfa3eb60384fc1060380be4b4'),
(5, 'ccarroll', '$2y$10$8DjRGlYa.I5xOH43fg9oTODU.nmJc9GG9KZwH3pdN0H5JdmzgQA9i', NULL, 'student', '2025-05-04 07:34:26', NULL),
(6, 'ntrickett79', '$2y$10$5kkrLllcSE494F9xKi7B0Ot6pPOBvUQgnEHRDQNiFC2whSkb4/dm.', 'ntrickett@sd79.bc.ca', 'teacher', '2025-05-04 07:52:29', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `show_id` (`show_id`);

--
-- Indexes for table `costumecategories`
--
ALTER TABLE `costumecategories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `costumes`
--
ALTER TABLE `costumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `ideas`
--
ALTER TABLE `ideas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `propcategories`
--
ALTER TABLE `propcategories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `props`
--
ALTER TABLE `props`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `showcostumes`
--
ALTER TABLE `showcostumes`
  ADD PRIMARY KEY (`show_id`,`costume_id`),
  ADD KEY `costume_id` (`costume_id`);

--
-- Indexes for table `showlines`
--
ALTER TABLE `showlines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_id`),
  ADD KEY `show_id` (`show_id`);

--
-- Indexes for table `showprops`
--
ALTER TABLE `showprops`
  ADD PRIMARY KEY (`show_id`,`prop_id`),
  ADD KEY `prop_id` (`prop_id`);

--
-- Indexes for table `shows`
--
ALTER TABLE `shows`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `studentcharacters`
--
ALTER TABLE `studentcharacters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `teacher_code` (`teacher_code`);

--
-- Indexes for table `teacher_links`
--
ALTER TABLE `teacher_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_teacher_id` (`lead_teacher_id`),
  ADD KEY `linked_teacher_id` (`linked_teacher_id`);

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
-- AUTO_INCREMENT for table `characters`
--
ALTER TABLE `characters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT for table `costumecategories`
--
ALTER TABLE `costumecategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `costumes`
--
ALTER TABLE `costumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ideas`
--
ALTER TABLE `ideas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `propcategories`
--
ALTER TABLE `propcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `props`
--
ALTER TABLE `props`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `showlines`
--
ALTER TABLE `showlines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shows`
--
ALTER TABLE `shows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `studentcharacters`
--
ALTER TABLE `studentcharacters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teacher_links`
--
ALTER TABLE `teacher_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`);

--
-- Constraints for table `costumes`
--
ALTER TABLE `costumes`
  ADD CONSTRAINT `costumes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `costumecategories` (`id`);

--
-- Constraints for table `props`
--
ALTER TABLE `props`
  ADD CONSTRAINT `props_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `propcategories` (`id`);

--
-- Constraints for table `showcostumes`
--
ALTER TABLE `showcostumes`
  ADD CONSTRAINT `showcostumes_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showcostumes_ibfk_2` FOREIGN KEY (`costume_id`) REFERENCES `costumes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `showlines`
--
ALTER TABLE `showlines`
  ADD CONSTRAINT `showlines_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`),
  ADD CONSTRAINT `showlines_ibfk_2` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`);

--
-- Constraints for table `showprops`
--
ALTER TABLE `showprops`
  ADD CONSTRAINT `showprops_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showprops_ibfk_2` FOREIGN KEY (`prop_id`) REFERENCES `props` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `studentcharacters`
--
ALTER TABLE `studentcharacters`
  ADD CONSTRAINT `studentcharacters_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `studentcharacters_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_links`
--
ALTER TABLE `teacher_links`
  ADD CONSTRAINT `teacher_links_ibfk_1` FOREIGN KEY (`lead_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_links_ibfk_2` FOREIGN KEY (`linked_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
