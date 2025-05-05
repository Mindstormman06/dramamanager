-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2025 at 07:20 AM
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
  `notes` text DEFAULT NULL
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
  `remember_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `costumecategories`
--
ALTER TABLE `costumecategories`
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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
