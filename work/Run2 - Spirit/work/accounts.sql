-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 22, 2026 at 11:01 AM
-- Server version: 10.6.22-MariaDB-cll-lve-log
-- PHP Version: 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `historic_listiary_development`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `usercode` varchar(16) NOT NULL,
  `password_hash` text NOT NULL,
  `is_bot` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_token` char(64) DEFAULT NULL,
  `session_token` char(64) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `username`, `email`, `usercode`, `password_hash`, `is_bot`, `is_active`, `created_at`, `verification_token`, `session_token`) VALUES
(1, 'testuser', 'vchernev91@abv.bg', '00000000', '$2y$10$2bjvz5Y/0y6U0caQbo09sedZiv9.BWguJFrjx.PkIRL6K0ys74dFO', 0, 0, '2026-02-15 08:54:41', '', NULL),
(2, 'katie', 'vchernev92@abv.bg', '7atHjZbV', '$2y$10$s0CKqGSezkn6XWuiCTDtxeGN0bJWeWkfxVZmYRfzDgTdeyHNV8CDq', 0, 0, '2026-02-19 18:25:50', '', NULL),
(3, 'David', 'vchernev93@abv.bg', 'uCR35Ldabfevyz9Z', '$2y$10$dscfQObBuVooroQ3vni13uexzpP3x47xEgmRCW9h.M5DyiDcVGmfG', 0, 0, '2026-02-19 18:29:54', '', NULL),
(4, 'jESTER', 'vchernev95@abv.bg', '4tnPDT5eSFsMQUQ3', '$2y$10$5hq2zCR3UIZuNPPLjklzx.fCBubII/GQcjqXMCJAXXi48Lu61uYnS', 0, 0, '2026-02-21 15:00:11', NULL, NULL),
(5, 'Malik', 'vchernev94@abv.bg', 'st8SIzgpmz39S0Iv', '$2y$10$sWqEiZM4kTbpVQe/RvKgQOGjbk5kbhKJMHP3PkS/ssFYS4XY.e4I.', 0, 0, '2026-02-21 15:02:12', '9a20904debd2956215fa8bef0d3c00bfb6125662e8ebb77513fd66209b064a6e', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `usercode` (`usercode`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
