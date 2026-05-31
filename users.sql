-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2026 at 07:55 AM
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
-- Database: `an`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `username` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `username`) VALUES
(1, 'Super Admin', 'admin@anime.test', NULL, '$2y$12$TxpeCT9zeBL5ah1vgUG1ee3knqCV34i85yz5Gb1OY93XYInGWJkya', 'ix2Tvj3whRGq162rFlFqyZ2I57ZVt2s3eIkFK84cb26wJKDYwxIgdMNlxSg0', '2026-05-17 22:20:46', '2026-05-17 22:20:46', 'super_admin', 'superadmin'),
(2, 'Super Admin', 'superadmin@superadmin.com', NULL, '$2y$12$iAEpRKxKp8UkPG1Wthyhq.E7A6ilbzd9oK//47t5tMM0PEp2pASAW', NULL, '2026-05-17 22:20:46', '2026-05-17 22:20:46', 'super_admin', 'superadmin2'),
(3, 'Admin', 'admin2@anime.test', NULL, '$2y$12$CSJhzepdWhxCjCr7eKL0C.bqF8BkQLs0mNE2z/MFOETKftzm8rOpC', NULL, '2026-05-17 22:20:46', '2026-05-17 22:20:46', 'admin', 'admin'),
(4, 'User', 'user@anime.test', NULL, '$2y$12$9v9o9UshgHnOpDHrYpeqk.fXcFukqIwhJHI.sN/QZcnLTIbsYXqti', NULL, '2026-05-17 22:20:46', '2026-05-17 22:20:46', 'user', 'user'),
(5, 'Demo User', 'demo@anime.test', NULL, '$2y$12$tI9UAHl6sJRebg/kMkPZLegj.5CSzTO/l65T.no3iblCOf/F6O0L.', NULL, '2026-05-17 22:20:47', '2026-05-17 22:20:47', 'user', 'demo');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
