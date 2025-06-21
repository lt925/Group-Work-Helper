-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2025 at 04:39 PM
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
-- Database: `groupworkhelper`
--

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `idGroup` int(255) UNSIGNED NOT NULL,
  `groupName` varchar(255) NOT NULL,
  `status` enum('archived','actived','inactive','') NOT NULL DEFAULT 'actived',
  `grouptype` varchar(10) NOT NULL,
  `description` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`idGroup`, `groupName`, `status`, `grouptype`, `description`, `createdAt`) VALUES
(1, 'ABB', 'actived', 'public', 'ABB', '2025-06-16 19:37:47'),
(2, 'ABCD', 'actived', 'public', '33333333', '2025-06-16 21:36:30'),
(3, 'qwert', 'actived', 'public', 'sdfdgsg', '2025-06-16 21:47:54'),
(4, 'GRAPE', 'actived', 'public', 'ojofjsdof', '2025-06-21 18:42:49'),
(5, 'BANANA', 'actived', 'public', '期望ertyuiop', '2025-06-21 21:37:24');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `idProject` int(255) UNSIGNED NOT NULL,
  `projectName` varchar(255) DEFAULT NULL,
  `startDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `endDate` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `idGroup` int(255) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`idProject`, `projectName`, `startDate`, `endDate`, `description`, `idGroup`) VALUES
(1, 'ABCD', '2025-06-16 11:47:57', '2025-06-19', 'ABCDEFG', 1),
(3, 'BANANA', '2025-06-06 16:00:00', '2025-06-28', 'banana', 1),
(4, 'APPLE', '2025-06-20 16:00:00', '2025-06-28', 'APPLE', 1),
(5, 'ABCDE', '2025-06-17 02:40:01', '2025-06-19', 'ABCDEFGH', 2),
(7, 'Grape', '2025-06-13 16:00:00', '2025-06-25', 'Grapie', 4),
(8, 'Grapie', '2025-06-06 16:00:00', '2025-06-28', 'GRaaic', 4),
(9, 'Banana', '2025-06-21 13:04:29', '0000-00-00', 'BA', 4),
(10, 'HA', '2025-06-21 13:05:18', '0000-00-00', 'h', 4),
(11, 'HANAAA', '2025-06-21 13:05:33', '0000-00-00', 'NA', 4),
(12, 'MANY', '2025-06-21 13:06:29', '0000-00-00', 'MNN', 4);

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `idTask` int(255) UNSIGNED NOT NULL,
  `idProject` int(255) UNSIGNED NOT NULL,
  `taskName` varchar(255) NOT NULL,
  `dueDate` date NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `idUser` int(255) UNSIGNED NOT NULL,
  `firstName` varchar(50) DEFAULT NULL,
  `lastName` varchar(50) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(20) NOT NULL,
  `phoneNum` varchar(20) DEFAULT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `createAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('Teacher','Student','Admin','') NOT NULL DEFAULT 'Student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`idUser`, `firstName`, `lastName`, `gender`, `dob`, `email`, `phoneNum`, `username`, `password`, `createAt`, `role`) VALUES
(1, 'John', 'Doe', 'preferNotToSay', '2013-03-21', 'adasdass@gmail.com', '011-1111-1111', 'LT', '$2y$10$A0LfA7IE9MrE7fQ1dsYf7uqGfINLEaIElzQ/FVXaYhs2itsTQuCqm', '2025-06-15 17:15:46', 'Teacher'),
(2, 'Johnny', 'Han', 'male', '2014-02-27', 'addosvsjv@gmail.com', '011-11393049', 'AB', '$2y$10$W6ejCiZq.3BDEgipibtgeua0RYNlkgoMXo8WK.t8rrrAF8YV1AbIG', '2025-06-16 11:34:20', 'Admin'),
(8, 'JI', 'ZHUA', 'preferNotToSay', '2025-06-12', 'adasdassss@gmail.com', '011-1111-1132', 'JIZHUA', '$2y$10$aNaTckmHSfZziNSiFRA/GeaMHcGnOt1c26.u9vyrU2Qfl0UEAqc5y', '2025-06-17 02:35:24', 'Student'),
(10, 'Abigail', 'Doe', 'preferNotToSay', '2025-06-05', 'adasdssss@gmail.com', '011-111-1122', 'ABB', '$2y$10$xuXMC.CKJiG9abJ7P4W.0.tjoE1CFMRoFbVu6t0TKAcIzWXKftxTa', '2025-06-17 04:29:12', 'Student'),
(11, 'Christina', 'Doe', 'female', '2025-06-14', 'adasbass@gmail.com', '011-1122-1111', 'ACC', '$2y$10$LtmnOgVoiWzM6LcWDJ8Pq.tnWo2coqkZD7olBVAmjC5FxFMMqdycG', '2025-06-20 16:40:06', 'Admin'),
(12, 'Christine', 'Doe', 'female', '2025-06-14', 'adascass@gmail.com', '011-1123-1111', 'ADD', '$2y$10$OzL.5UmSywIw2f5yrhhrXeBOaWPh52cVvAEupP0twlQaG5x/FakEy', '2025-06-20 16:43:22', 'Admin'),
(13, 'Christines', 'Doe', 'preferNotToSay', '2025-06-06', 'adaseass@gmail.com', '011-1111-1233', 'AEE', '$2y$10$yU8Bhiw4sf7AAJknkDXEpufkhXYfaoql93nQ/8q5EetY4yeJzapcy', '2025-06-20 16:44:10', 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE `user_group` (
  `idUser` int(10) UNSIGNED NOT NULL,
  `idGroup` int(10) UNSIGNED NOT NULL,
  `role` enum('captain','admin','teacher','student') NOT NULL DEFAULT 'student',
  `canAddTask` tinyint(1) NOT NULL DEFAULT 0,
  `canDeleteTask` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_group`
--

INSERT INTO `user_group` (`idUser`, `idGroup`, `role`, `canAddTask`, `canDeleteTask`) VALUES
(10, 5, 'captain', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_project`
--

CREATE TABLE `user_project` (
  `id` int(10) UNSIGNED NOT NULL,
  `idProject` int(10) UNSIGNED NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL,
  `submitted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_project`
--

INSERT INTO `user_project` (`id`, `idProject`, `idUser`, `submitted`) VALUES
(4, 1, 1, 0),
(5, 3, 1, 1),
(6, 4, 1, 1),
(8, 1, 8, 0),
(9, 3, 8, 0),
(10, 4, 8, 0),
(12, 5, 8, 0),
(13, 5, 2, 1),
(16, 5, 1, 0),
(17, 3, 11, 0),
(18, 7, 11, 0),
(19, 8, 11, 0),
(20, 7, 11, 0),
(21, 8, 11, 0),
(22, 9, 11, 0),
(23, 10, 11, 0),
(24, 11, 11, 0),
(25, 12, 11, 0),
(26, 5, 11, 0),
(28, 4, 11, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`idGroup`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`idProject`),
  ADD KEY `project-group` (`idGroup`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`idTask`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_group`
--
ALTER TABLE `user_group`
  ADD PRIMARY KEY (`idUser`,`idGroup`),
  ADD KEY `group` (`idGroup`);

--
-- Indexes for table `user_project`
--
ALTER TABLE `user_project`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project` (`idProject`),
  ADD KEY `user` (`idUser`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `idGroup` int(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `idProject` int(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `idTask` int(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `idUser` int(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_project`
--
ALTER TABLE `user_project`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `project-group` FOREIGN KEY (`idGroup`) REFERENCES `groups` (`idGroup`) ON DELETE CASCADE;

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `project-task` FOREIGN KEY (`idProject`) REFERENCES `project` (`idProject`);

--
-- Constraints for table `user_group`
--
ALTER TABLE `user_group`
  ADD CONSTRAINT `group` FOREIGN KEY (`idGroup`) REFERENCES `groups` (`idGroup`) ON DELETE CASCADE,
  ADD CONSTRAINT `users` FOREIGN KEY (`idUser`) REFERENCES `users` (`idUser`) ON DELETE CASCADE;

--
-- Constraints for table `user_project`
--
ALTER TABLE `user_project`
  ADD CONSTRAINT `project` FOREIGN KEY (`idProject`) REFERENCES `project` (`idProject`),
  ADD CONSTRAINT `user` FOREIGN KEY (`idUser`) REFERENCES `users` (`idUser`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
