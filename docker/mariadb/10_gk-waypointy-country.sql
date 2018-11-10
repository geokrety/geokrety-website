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
-- Table structure for table `gk-waypointy-country`
--

CREATE TABLE `gk-waypointy-country` (
  `kraj` varchar(200) COLLATE utf8_polish_ci NOT NULL,
  `country` varchar(200) COLLATE utf8_polish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Dumping data for table `gk-waypointy-country`
--

INSERT INTO `gk-waypointy-country` (`kraj`, `country`) VALUES
('Afghanistan', 'Afghanistan'),
('Ägypten', 'Egypt'),
('Albania', 'Albania'),
('Albanien', 'Albania'),
('Amerikanisch-Ozeanien', 'American Samoa'),
('Argentinien', 'Argentina'),
('Argentyna', 'Argentina'),
('Armenia', 'Armenia'),
('AT', 'Austria'),
('AU', 'Australia'),
('Australien', 'Australia'),
('Austria', 'Australia'),
('Bahamas', 'Bahamas'),
('Belarus (Weißrußland)', 'Belarus'),
('Belgia', 'Belgium'),
('Belgien', 'Belgium'),
('Białoruś', 'Belarus'),
('Bosnien-Herzegowina', 'Bosnia and Herzegovina'),
('Botsuana', 'Botswana'),
('Brasilien', 'Brazil'),
('Bulgarien', 'Bulgaria'),
('Bułgaria', 'Bulgaria'),
('CA', 'Canada'),
('Canada', 'Canada'),
('Česká Republika', 'Czech Republic'),
('Chile', 'Chile'),
('Chiny', 'China'),
('Chorwacja', 'Croatia'),
('Costa Rica', 'Costa Rica'),
('Cypr', 'Cyprus'),
('CZ', 'Czech Republic'),
('Czeska Republika', 'Czech Republic'),
('Dänemark', 'Denmark'),
('Dania', 'Denmark'),
('DE', 'Germany'),
('Demokratische Volksrepublik Korea', 'North Korea'),
('Denmark', 'Denmark'),
('Deutschland', 'Germany'),
('Dominikanische Republik', 'Dominican Republic'),
('Ecuador', 'Ecuador'),
('Egipt', 'Egypt'),
('El Salvador', 'El Salvador'),
('ES', 'Spain'),
('Estland', 'Estonia'),
('Estonia', 'Estonia'),
('Falklandinseln', 'Falkland Islands'),
('Färöer (zu Dänemark)', 'Faroe Islands'),
('Finlandia', 'Finland'),
('Finnland', 'Finland'),
('France', 'France'),
('Francja', 'France'),
('Frankreich', 'France'),
('Georgien', 'Georgia'),
('Germany', 'Germany'),
('Gibraltar', 'Gibraltar'),
('Granada', 'Granada'),
('Grecja', 'Greece'),
('Greenland', 'Greenland'),
('Griechenland', 'Greece'),
('Grönland', 'Greenland'),
('Großbritannien', 'United Kingdom'),
('Guatemala', 'Guatemala'),
('Hiszpania', 'Spain'),
('Holandia', 'Netherlands'),
('Honduras', 'Honduras'),
('HR', 'Croatia'),
('ID', 'Indonesia'),
('IN', 'India'),
('Indie', 'India'),
('Indien', 'India'),
('Irland', 'Ireland'),
('Irlandia', 'Ireland'),
('Island', 'Iceland'),
('Islandia', 'Iceland'),
('Israel', 'Israel'),
('Italien', 'Italy'),
('Japan', 'Japan'),
('Jemen', 'Yemen'),
('Jordanien', 'Jordan'),
('Kambodża', 'Cambodia'),
('Kanada', 'Canada'),
('Kapverden', 'Cape Verde'),
('Kasachstan', 'Kazakhstan'),
('Kenia', 'Kenya'),
('KG', 'Kyrgyzstan'),
('Kirgistan', 'Kyrgyzstan'),
('Kolumbien', 'Colombia'),
('Kroatien', 'Croatia'),
('Kuba', 'Cuba'),
('Laos', 'Laos'),
('Lettland', 'Latvia'),
('Liechtenstein', 'Liechtenstein'),
('Litauen', 'Lithuania'),
('Litwa', 'Lithuania'),
('Luxemburg', 'Luxembourg'),
('Łotwa', 'Latvia'),
('Malaysia', 'Malaysia'),
('Malediven', 'Maldives'),
('Malta', 'Malta'),
('Marokko', 'Morocco'),
('Maroko', 'Morocco'),
('Mauritius', 'Mauritius'),
('Mazedonien', 'Macedonia'),
('Mexico', 'Mexico'),
('Mexiko', 'Mexico'),
('Mołdawia', 'Moldova'),
('Monako', 'Monaco'),
('Montenegro', 'Montenegro'),
('Namibia', 'Namibia'),
('Nepal', 'Nepal'),
('Neuseeland', 'New Zealand'),
('Nicaragua', 'Nicaragua'),
('Niderlandy', 'Netherlands'),
('Niederlande', 'Netherlands'),
('Niederländische Antillen', 'Netherlands Antilles'),
('Niemcy', 'Germany'),
('NO', 'Norway'),
('Norwegen', 'Norway'),
('Norwegia', 'Norway'),
('Österreich', 'Austria'),
('Panama', 'Panama'),
('Papua-Neuguinea', 'Papua New Guinea'),
('Philippinen', 'Philippines'),
('PL', 'Poland'),
('Polen', 'Poland'),
('Polska', 'Poland'),
('Portugal', 'Portugal'),
('Portugalia', 'Portugal'),
('Rosja', 'Russia'),
('Ruanda', 'Rwanda'),
('Rumänien', 'Romania'),
('Rumunia', 'Romania'),
('Russische Föderation', 'Russia'),
('Sambia', 'Zambia'),
('Saudi-Arabien', 'Saudi Arabia'),
('Schweden', 'Sweden'),
('Schweiz', 'Switzerland'),
('SE', 'Sweden'),
('Serbien', 'Serbia'),
('Serbien und Montenegro', 'Serbia and Montenegro'),
('Seychellen', 'Seychelles'),
('SI', 'Slovenia'),
('Simbabwe', 'Zimbabwe'),
('SK', 'Slovakia'),
('Slowakai', 'Slovakia'),
('Slowenien', 'Slovenia'),
('Słowacja', 'Slovakia'),
('Soviet Union', 'Soviet Union'),
('Spain', 'Spain'),
('Spanien', 'Spain'),
('Sri Lanka', 'Sri Lanka'),
('Südafrika', 'South Africa'),
('Sudan', 'Sudan'),
('Südgeorgien und die Südlichen Sandwichinseln', 'South Georgia and the South Sandwich Islands'),
('Svalbard und Jan Mayen', 'Svalbard and Jan Mayen'),
('Sweden', 'Sweden'),
('Syrien', 'Syria'),
('Szwajcaria', 'Switzerland'),
('Szwecja', 'Sweden'),
('Taiwan', 'Taiwan'),
('Tajlandia', 'Thailand'),
('Tansania', 'Tanzania'),
('Thailand', 'Thailand'),
('Togo', 'Togo'),
('Trinidad und Tobago', 'Trinidad and Tobago'),
('Tschad', 'Chad'),
('Tschechische Republik', 'Czech Republic'),
('Tunesien', 'Tunisia'),
('Tunezja', 'Tunisia'),
('Turcja', 'Turkey'),
('Türkei', 'Turkey'),
('UA', 'Ukraine'),
('Uganda', 'Uganda'),
('UK', 'United Kingdom'),
('Ukraina', 'Ukraine'),
('Ukraine', 'Ukraine'),
('Ungarn', 'Hungary'),
('United States', 'United States'),
('US', 'United States'),
('USA', 'USA'),
('Usbekistan', 'Uzbekistan'),
('Vatikan (Heiliger Stuhl)', 'Holy See'),
('Vereinigte Arabische Emirate', 'United Arab Emirates'),
('Vereinigte Staaten', 'United States'),
('Vietnam', 'Vietnam'),
('Virgin Islands (U.S.)', 'United States Virgin Islands'),
('Volksrepublik China', 'China'),
('Węgry', 'Hungary'),
('Wielka Brytania', 'United Kingdom'),
('Wietnam', 'Vietnam'),
('Włochy', 'Italy'),
('Zjednoczone Emiraty Arabskie', 'United Arab Emirates'),
('Zypern', 'Cyprus');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gk-waypointy-country`
--
ALTER TABLE `gk-waypointy-country`
  ADD UNIQUE KEY `unique_kraj` (`kraj`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
