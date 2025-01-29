-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2023 at 06:05 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prodaja_karata`
--

-- --------------------------------------------------------

--
-- Table structure for table `customerhasticket`
--

CREATE TABLE `customerhasticket` (
  `id` int(10) UNSIGNED NOT NULL,
  `customerId` int(10) UNSIGNED NOT NULL,
  `ticketId` int(10) UNSIGNED NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customerhasticket`
--

INSERT INTO `customerhasticket` (`id`, `customerId`, `ticketId`, `amount`) VALUES
(1, 2, 1, 3),
(2, 1, 1, 4),
(4, 4, 2, 1),
(6, 2, 2, 1),
(10, 5, 1, 1),
(11, 5, 4, 1),
(12, 5, 19, 4);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(25) NOT NULL,
  `surname` varchar(25) NOT NULL,
  `email` varchar(40) NOT NULL,
  `class` enum('Admin','Menadzer','Korisnik','') NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `surname`, `email`, `class`, `balance`, `password`) VALUES
(1, 'Mladen', 'Petrovic', 'mladenp159@gmail.com', 'Admin', 1000.00, '827ccb0eea8a706c4c34a16891f84e7b'),
(2, 'Petar', 'Peric', 'perap@gmail.com', 'Korisnik', 1000.00, '827ccb0eea8a706c4c34a16891f84e7b'),
(4, 'Marko', 'Jankovic', 'Markojan@gmail.com', 'Korisnik', 800.00, '827ccb0eea8a706c4c34a16891f84e7b'),
(5, 'Jelena', 'Protic', 'jelenaprotic667@gmail.com', 'Menadzer', 85200.00, '827ccb0eea8a706c4c34a16891f84e7b'),
(9, 'Milan', 'Milanovic', 'milanm@gmail.com', 'Korisnik', 0.00, '827ccb0eea8a706c4c34a16891f84e7b'),
(10, 'Marko', 'Ivanovic', 'mivanovic@gmail.com', 'Korisnik', 0.00, '827ccb0eea8a706c4c34a16891f84e7b');

-- --------------------------------------------------------

--
-- Table structure for table `stadiums`
--

CREATE TABLE `stadiums` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL,
  `info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stadiums`
--

INSERT INTO `stadiums` (`id`, `name`, `info`) VALUES
(1, 'Rajko Mitic', 'Stadion Crvene Zvezde'),
(2, 'JNA', 'Stadion Partizana'),
(3, 'Vozdovac - gradski stadion', 'Stadion Vozdovca'),
(4, 'Mladost stadion', 'Stadion Mladosti iz Lucana'),
(5, 'Cair', 'Stadion Radnickog iz Nisa'),
(6, 'TSC Arena', 'Stadion TSC-a');

-- --------------------------------------------------------

--
-- Table structure for table `support`
--

CREATE TABLE `support` (
  `id` int(11) NOT NULL,
  `senderId` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `reply` longtext DEFAULT NULL,
  `answered` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support`
--

INSERT INTO `support` (`id`, `senderId`, `message`, `reply`, `answered`) VALUES
(1, 2, 'Imam problem sa prijavljivanjem', 'Problem je resen', 1),
(2, 2, 'problem', 'Odgovor', 1),
(3, 2, 'Imam Problem sa uplatom', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ticketabs`
--

CREATE TABLE `ticketabs` (
  `id` int(10) UNSIGNED NOT NULL,
  `orientation` enum('Sever','Jug','Zapad','Istok','VIP') NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticketabs`
--

INSERT INTO `ticketabs` (`id`, `orientation`, `price`) VALUES
(1, 'Sever', 500.00),
(2, 'Jug', 400.00),
(3, 'Sever', 300.00),
(4, 'Zapad', 700.00),
(30, 'Jug', 400.00),
(37, 'Sever', 600.00),
(38, 'Sever', 800.00);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `stadiumId` int(10) UNSIGNED NOT NULL,
  `ticketId` int(10) UNSIGNED NOT NULL,
  `time` datetime NOT NULL,
  `game` text NOT NULL,
  `amount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `stadiumId`, `ticketId`, `time`, `game`, `amount`) VALUES
(1, 1, 1, '2023-11-05 16:00:00', 'Crvena Zvezda - Radnik Surdulica', 382),
(2, 1, 2, '2023-11-05 16:00:00', 'Crvena Zvezda - Radnik Surdulica', 295),
(3, 1, 4, '2023-11-05 16:00:00', 'Crvena Zvezda - Radnik Surdulica', 300),
(4, 2, 30, '2023-10-24 20:00:00', 'Partizan - Radnicki Nis', 699),
(5, 2, 1, '2023-10-24 20:00:00', 'Partizan - Radnicki Nis', 600),
(10, 2, 1, '2023-10-24 20:00:00', 'Partizan - Radnicki Nis', 600),
(12, 4, 37, '2023-11-15 18:15:00', 'Mladost Lucani - Crvena Zvezda', 500),
(18, 1, 38, '2023-12-13 20:20:00', 'Crvena Zvezda - Partizan', 700),
(19, 3, 2, '2023-10-26 20:30:00', 'Vozdovac - Partizan', 267);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customerhasticket`
--
ALTER TABLE `customerhasticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `korisnik_id` (`customerId`),
  ADD KEY `kartaref_id` (`ticketId`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stadiums`
--
ALTER TABLE `stadiums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support`
--
ALTER TABLE `support`
  ADD PRIMARY KEY (`id`),
  ADD KEY `senderId` (`senderId`);

--
-- Indexes for table `ticketabs`
--
ALTER TABLE `ticketabs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stadion_id` (`stadiumId`),
  ADD KEY `karta_id` (`ticketId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customerhasticket`
--
ALTER TABLE `customerhasticket`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stadiums`
--
ALTER TABLE `stadiums`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `support`
--
ALTER TABLE `support`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ticketabs`
--
ALTER TABLE `ticketabs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customerhasticket`
--
ALTER TABLE `customerhasticket`
  ADD CONSTRAINT `customerhasticket_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `customerhasticket_ibfk_2` FOREIGN KEY (`ticketId`) REFERENCES `tickets` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`stadiumId`) REFERENCES `stadiums` (`id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`ticketId`) REFERENCES `ticketabs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
