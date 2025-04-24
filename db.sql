-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 27, 2025 at 10:58 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bnm`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `confirmPassword` varchar(255) DEFAULT NULL,
  `Role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `confirmPassword`, `Role`) VALUES
(32, 'hichem', 'bnmadmin/2025', 'bnmadmin/2025', 'Admin'),
(33, 'admin', '911admin', '911admin', 'Developer'),
(34, 'nabil', 'nabil1230', 'nabil1230', 'Admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE daily_profit (
    id SERIAL PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    fonds_propre NUMERIC,
    dettes_fournisseur NUMERIC,
    creance_client NUMERIC,
    total_stock NUMERIC,
    caisse NUMERIC,
    banque NUMERIC,
    total_tresorerie NUMERIC
);

CREATE TABLE hourly_profit (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fonds_propre NUMERIC,
    dettes_fournisseur NUMERIC,
    creance_client NUMERIC,
    total_stock NUMERIC,
    caisse NUMERIC,
    banque NUMERIC,
    total_tresorerie NUMERIC
);

CREATE INDEX idx_hourly_profit_timestamp ON hourly_profit(timestamp);
