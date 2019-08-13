-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:33061
-- Generation Time: Aug 13, 2019 at 12:05 PM
-- Server version: 5.7.25
-- PHP Version: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fci`
--

-- --------------------------------------------------------

--
-- Table structure for table `binaries`
--

CREATE TABLE `binaries` (
  `id` int(11) DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `path` varchar(12288) DEFAULT NULL,
  `level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `binaries`
--

INSERT INTO `binaries` (`id`, `parent_id`, `position`, `path`, `level`) VALUES
(1, 0, 1, '1', 1),
(2, 1, 2, '1.2', 2),
(3, 2, 1, '1.2.3', 3),
(4, 2, 2, '1.2.4', 3),
(5, 3, 1, '1.2.3.5', 4),
(6, 3, 2, '1.2.3.6', 4),
(7, 4, 1, '1.2.4.7', 4),
(8, 4, 2, '1.2.4.8', 4),
(9, 5, 1, '1.2.3.5.9', 5),
(10, 5, 2, '1.2.3.5.10', 5),
(11, 6, 1, '1.2.3.6.11', 5),
(12, 6, 2, '1.2.3.6.12', 5),
(13, 7, 1, '1.2.4.7.13', 5),
(14, 7, 2, '1.2.4.7.14', 5),
(15, 8, 1, '1.2.4.8.15', 5),
(16, 8, 2, '1.2.4.8.16', 5),
(17, 16, 1, '1.2.4.8.16.17', 6),
(18, 17, 1, '1.2.4.8.16.17.18', 7),
(19, 17, 2, '1.2.4.8.16.17.19', 7),
(20, 18, 1, '1.2.4.8.16.17.18.20', 8),
(21, 18, 2, '1.2.4.8.16.17.18.21', 8),
(22, 19, 1, '1.2.4.8.16.17.19.22', 8),
(23, 19, 2, '1.2.4.8.16.17.19.23', 8),
(24, 20, 1, '1.2.4.8.16.17.18.20.24', 9),
(25, 20, 2, '1.2.4.8.16.17.18.20.25', 9),
(26, 21, 1, '1.2.4.8.16.17.18.21.26', 9),
(27, 21, 2, '1.2.4.8.16.17.18.21.27', 9),
(28, 22, 1, '1.2.4.8.16.17.19.22.28', 9),
(29, 22, 2, '1.2.4.8.16.17.19.22.29', 9),
(30, 23, 1, '1.2.4.8.16.17.19.23.30', 9),
(31, 23, 2, '1.2.4.8.16.17.19.23.31', 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `binaries`
--
ALTER TABLE `binaries`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `parent_id` (`parent_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
