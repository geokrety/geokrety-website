USE `geokrety-db`;

-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Jul 12, 2018 at 06:46 PM
-- Server version: 10.1.13-MariaDB-1~jessie
-- PHP Version: 7.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `gk-waypointy-type`
--

CREATE TABLE `gk-waypointy-type` (
  `typ` varchar(200) COLLATE utf8_polish_ci NOT NULL,
  `cache_type` varchar(200) COLLATE utf8_polish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Dumping data for table `gk-waypointy-type`
--

INSERT INTO `gk-waypointy-type` (`typ`, `cache_type`) VALUES
('beweglicher Cache', 'Mobile'),
('BIT Cache', 'BIT Cache'),
('Cemetery', 'Cemetery'),
('Drive-In', 'Drive-In'),
('Drive-In-Cache', 'Drive-In'),
('Event', 'Event'),
('Event Cache', 'Event'),
('Event-Cache', 'Event'),
('Geocache', 'Traditional'),
('Geocache|Event Cache', 'Event'),
('Geocache|Multi-cache', 'Multicache'),
('Geocache|Mystery Cache', 'Mystery'),
('Geocache|Traditional Cache', 'Traditional'),
('Geocache|Unknown Cache', 'Unknown cache'),
('Geocache|Virtual Cache', 'Virtual'),
('Geocache|Webcam Cache', 'Webcam'),
('Guest Book', 'Guest Book'),
('Inny typ skrzynki', 'Other'),
('kvíz', 'Quiz'),
('Letterbox', 'Letterbox'),
('Mathe-/Physikcache', 'Math / physics cache'),
('Medical Facility', 'Medical Facility'),
('Mobilna', 'Mobile'),
('Moving', 'Mobile'),
('Moving Cache', 'Mobile'),
('MP3 (Podcache)', 'MP3'),
('Multi', 'Multicache'),
('Multicache', 'Multicache'),
('neznámá', 'Unknown cache'),
('normaler Cache', 'Traditional'),
('Other', 'Other'),
('Own cache', 'Own cache'),
('Podcast cache', 'Podcast cache'),
('Quiz', 'Quiz'),
('Rätselcache', 'Mystery'),
('Skrzynka nietypowa', 'Unusual box'),
('tradiční', 'Traditional'),
('Traditional', 'Traditional'),
('Traditional Cache', 'Traditional'),
('Tradycyjna', 'Traditional'),
('unbekannter Cachetyp', 'Unknown cache'),
('Unknown type', 'Unknown cache'),
('USB (Dead Drop)', 'USB'),
('Virtual', 'Virtual'),
('Virtual Cache', 'Virtual'),
('virtueller Cache', 'Virtual'),
('Webcam', 'Webcam'),
('Webcam Cache', 'Webcam'),
('Webcam-Cache', 'Webcam'),
('Wirtualna', 'Virtual'),
('Wydarzenie', 'Event');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gk-waypointy-type`
--
ALTER TABLE `gk-waypointy-type`
  ADD UNIQUE KEY `unique_typ` (`typ`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
