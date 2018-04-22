CREATE DATABASE  IF NOT EXISTS `geokrety-db` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `geokrety-db`;

DROP TABLE IF EXISTS `gk-waypointy-type`;
CREATE TABLE `gk-waypointy-type` (
  `typ` varchar(200) COLLATE utf8_polish_ci NOT NULL,
  `cache_type` varchar(200) COLLATE utf8_polish_ci DEFAULT NULL,
  UNIQUE KEY `unique_typ` (`typ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

INSERT INTO `gk-waypointy-type` (`typ`, `cache_type`) VALUES
('beweglicher Cache',	'Mobile'),
('Drive-In-Cache',	'Drive-In'),
('Event-Cache',	'Event'),
('Mathe-/Physikcache',	'Math / physics cache'),
('Mobilna',	'Mobile'),
('Multicache',	'Multicache'),
('normaler Cache',	'Traditional'),
('Own cache',	'Own cache'),
('Podcast cache',	'Podcast cache'),
('Quiz',	'Quiz'),
('Rätselcache',	'Mystery'),
('Skrzynka nietypowa',	'Unusual box'),
('Tradycyjna',	'Traditional'),
('unbekannter Cachetyp',	'Unknown cache'),
('virtueller Cache',	'Virtual'),
('Webcam',	'Webcam'),
('Webcam-Cache',	'Webcam'),
('Wirtualna',	'Virtual'),
('Wydarzenie',	'Event')
ON DUPLICATE KEY UPDATE `typ` = VALUES(`typ`), `cache_type` = VALUES(`cache_type`);