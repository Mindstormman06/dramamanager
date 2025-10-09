-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2025 at 02:33 AM
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
-- Table structure for table `album_photos`
--

CREATE TABLE `album_photos` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `showid` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `changelog`
--

CREATE TABLE `changelog` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_students`
--

CREATE TABLE `class_students` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `costumecategories`
--

CREATE TABLE `costumecategories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `costumecharacters`
--

CREATE TABLE `costumecharacters` (
  `id` int(11) NOT NULL,
  `costume_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `rehearsals`
--

CREATE TABLE `rehearsals` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rehearsal_participants`
--

CREATE TABLE `rehearsal_participants` (
  `rehearsal_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(2, 'costumes'),
(4, 'props'),
(3, 'stage_crew');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `showid` varchar(255) NOT NULL,
  `character_id` int(11) DEFAULT NULL,
  `crew` tinyint(1) DEFAULT 0,
  `scheduled_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_alerts`
--

CREATE TABLE `schedule_alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `email_hash` varchar(255) NOT NULL,
  `alert_day_before` tinyint(1) DEFAULT 0,
  `alert_hours_before` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `showcostumes`
--

CREATE TABLE `showcostumes` (
  `show_id` int(11) NOT NULL,
  `costume_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discord_username` varchar(37) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_roles`
--

CREATE TABLE `student_roles` (
  `student_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `remember_token` varchar(64) DEFAULT NULL,
  `reset_requested` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `album_photos`
--
ALTER TABLE `album_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `changelog`
--
ALTER TABLE `changelog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `show_id` (`show_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_students`
--
ALTER TABLE `class_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_id` (`class_id`,`student_id`);

--
-- Indexes for table `costumecategories`
--
ALTER TABLE `costumecategories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `costumecharacters`
--
ALTER TABLE `costumecharacters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `costume_id` (`costume_id`),
  ADD KEY `character_id` (`character_id`);

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
-- Indexes for table `rehearsals`
--
ALTER TABLE `rehearsals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rehearsal_participants`
--
ALTER TABLE `rehearsal_participants`
  ADD PRIMARY KEY (`rehearsal_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_alerts`
--
ALTER TABLE `schedule_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`);

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
-- Indexes for table `student_roles`
--
ALTER TABLE `student_roles`
  ADD PRIMARY KEY (`student_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

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
-- AUTO_INCREMENT for table `album_photos`
--
ALTER TABLE `album_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `changelog`
--
ALTER TABLE `changelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `characters`
--
ALTER TABLE `characters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_students`
--
ALTER TABLE `class_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `costumecategories`
--
ALTER TABLE `costumecategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `costumecharacters`
--
ALTER TABLE `costumecharacters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `costumes`
--
ALTER TABLE `costumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ideas`
--
ALTER TABLE `ideas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `propcategories`
--
ALTER TABLE `propcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `props`
--
ALTER TABLE `props`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rehearsals`
--
ALTER TABLE `rehearsals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_alerts`
--
ALTER TABLE `schedule_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `showlines`
--
ALTER TABLE `showlines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shows`
--
ALTER TABLE `shows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `studentcharacters`
--
ALTER TABLE `studentcharacters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_links`
--
ALTER TABLE `teacher_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `album_photos`
--
ALTER TABLE `album_photos`
  ADD CONSTRAINT `album_photos_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`);

--
-- Constraints for table `costumecharacters`
--
ALTER TABLE `costumecharacters`
  ADD CONSTRAINT `costumecharacters_ibfk_1` FOREIGN KEY (`costume_id`) REFERENCES `costumes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `costumecharacters_ibfk_2` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `rehearsal_participants`
--
ALTER TABLE `rehearsal_participants`
  ADD CONSTRAINT `rehearsal_participants_ibfk_1` FOREIGN KEY (`rehearsal_id`) REFERENCES `rehearsals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rehearsal_participants_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_alerts`
--
ALTER TABLE `schedule_alerts`
  ADD CONSTRAINT `schedule_alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `schedule_alerts_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`id`);

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
-- Constraints for table `student_roles`
--
ALTER TABLE `student_roles`
  ADD CONSTRAINT `student_roles_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

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
