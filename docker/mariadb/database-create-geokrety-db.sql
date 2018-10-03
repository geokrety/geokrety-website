CREATE DATABASE  IF NOT EXISTS `geokrety-db` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `geokrety-db`;

-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Apr 30, 2018 at 09:30 PM
-- Server version: 10.1.13-MariaDB-1~jessie
-- PHP Version: 7.2.4

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `geokrety`
--

-- --------------------------------------------------------

--
-- Table structure for table `captcha_codes`
--

CREATE TABLE `captcha_codes` (
  `id` varchar(40) COLLATE utf8mb4_bin NOT NULL,
  `namespace` varchar(32) COLLATE utf8mb4_bin NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_bin NOT NULL,
  `code_display` varchar(32) COLLATE utf8mb4_bin NOT NULL,
  `created` int(11) NOT NULL,
  `audio_data` mediumblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `gk-aktywnekody`
--

CREATE TABLE `gk-aktywnekody` (
  `kod` varchar(60) COLLATE utf8_polish_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-aktywnemaile`
--

CREATE TABLE `gk-aktywnemaile` (
  `kod` varchar(60) COLLATE utf8_polish_ci NOT NULL,
  `userid` bigint(20) NOT NULL,
  `email` varchar(150) COLLATE utf8_polish_ci NOT NULL,
  `done` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1=confirmed',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-aktywnesesje`
--

CREATE TABLE `gk-aktywnesesje` (
  `sessid` varchar(200) COLLATE utf8_polish_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userid` bigint(20) NOT NULL,
  `user` varchar(30) COLLATE utf8_polish_ci NOT NULL,
  `remember` binary(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-badges`
--

CREATE TABLE `gk-badges` (
  `id` int(11) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `desc` varchar(128) COLLATE utf8_polish_ci NOT NULL,
  `file` varchar(32) COLLATE utf8_polish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci COMMENT='badges for the users';

-- --------------------------------------------------------

--
-- Table structure for table `gk-errory`
--

CREATE TABLE `gk-errory` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` varchar(50) COLLATE utf8_polish_ci NOT NULL DEFAULT '',
  `userid` int(10) UNSIGNED NOT NULL,
  `ip` varchar(16) COLLATE utf8_polish_ci NOT NULL,
  `file` text COLLATE utf8_polish_ci NOT NULL,
  `details` mediumtext COLLATE utf8_polish_ci NOT NULL,
  `severity` int(10) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-geokrety`
--

CREATE TABLE `gk-geokrety` (
  `id` int(10) UNSIGNED NOT NULL,
  `nr` varchar(9) COLLATE utf8_polish_ci NOT NULL,
  `nazwa` varchar(75) COLLATE utf8_polish_ci DEFAULT NULL,
  `opis` text COLLATE utf8_polish_ci,
  `owner` int(10) UNSIGNED DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `droga` int(10) UNSIGNED NOT NULL,
  `skrzynki` smallint(5) UNSIGNED NOT NULL,
  `zdjecia` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `ost_pozycja_id` int(10) UNSIGNED NOT NULL,
  `ost_log_id` int(10) UNSIGNED NOT NULL,
  `hands_of` int(10) DEFAULT NULL COMMENT 'In the hands of user',
  `missing` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `typ` enum('0','1','2','3','4') COLLATE utf8_polish_ci NOT NULL,
  `avatarid` int(10) UNSIGNED NOT NULL,
  `timestamp_oc` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-geokrety-rating`
--

CREATE TABLE `gk-geokrety-rating` (
  `id` bigint(20) NOT NULL COMMENT 'id kreta',
  `userid` bigint(20) NOT NULL COMMENT 'id usera',
  `rate` float NOT NULL DEFAULT '0' COMMENT 'single rating (number of stars)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci COMMENT='GK ratings';

-- --------------------------------------------------------

--
-- Table structure for table `gk-grupy`
--

CREATE TABLE `gk-grupy` (
  `groupid` bigint(20) NOT NULL,
  `kretid` bigint(20) NOT NULL,
  `joined` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci COMMENT='wchich kret belongs to which group';

-- --------------------------------------------------------

--
-- Table structure for table `gk-grupy-desc`
--

CREATE TABLE `gk-grupy-desc` (
  `groupid` bigint(20) NOT NULL,
  `creator` bigint(20) NOT NULL,
  `created` datetime DEFAULT NULL,
  `private` binary(1) NOT NULL,
  `desc` blob NOT NULL,
  `name` varchar(128) COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci COMMENT='descriptions of groups';

-- --------------------------------------------------------

--
-- Table structure for table `gk-licznik`
--

CREATE TABLE `gk-licznik` (
  `witryna` varchar(20) COLLATE utf8_polish_ci NOT NULL DEFAULT '',
  `licznik` bigint(20) NOT NULL,
  `od` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-maile`
--

CREATE TABLE `gk-maile` (
  `id_maila` bigint(20) NOT NULL,
  `random_string` varchar(10) COLLATE utf8_polish_ci NOT NULL,
  `from` bigint(20) NOT NULL,
  `to` bigint(20) NOT NULL,
  `temat` varchar(255) COLLATE utf8_polish_ci NOT NULL,
  `tresc` text COLLATE utf8_polish_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(50) COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-miasta`
--

CREATE TABLE `gk-miasta` (
  `id` bigint(20) NOT NULL,
  `name` varchar(130) COLLATE utf8_polish_ci NOT NULL,
  `asciiname` varchar(130) COLLATE utf8_polish_ci NOT NULL,
  `alternatenames` varchar(500) COLLATE utf8_polish_ci NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `country` varchar(3) COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-news`
--

CREATE TABLE `gk-news` (
  `news_id` bigint(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `czas_postu` datetime NOT NULL,
  `tytul` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `tresc` mediumtext COLLATE utf8_polish_ci,
  `who` varchar(80) COLLATE utf8_polish_ci NOT NULL,
  `userid` int(10) NOT NULL,
  `komentarze` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `ostatni_komentarz` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-news-comments`
--

CREATE TABLE `gk-news-comments` (
  `comment_id` int(10) UNSIGNED NOT NULL,
  `news_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `date` datetime NOT NULL,
  `comment` varchar(1000) COLLATE utf8_polish_ci NOT NULL,
  `icon` tinyint(3) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-news-comments-access`
--

CREATE TABLE `gk-news-comments-access` (
  `news_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `read` datetime DEFAULT NULL,
  `post` datetime DEFAULT NULL,
  `subscribed` enum('0','1') COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-obrazki`
--

CREATE TABLE `gk-obrazki` (
  `typ` tinyint(3) UNSIGNED DEFAULT NULL,
  `obrazekid` int(10) UNSIGNED NOT NULL,
  `id` int(10) UNSIGNED NOT NULL,
  `id_kreta` int(10) UNSIGNED NOT NULL,
  `user` int(10) UNSIGNED NOT NULL,
  `plik` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `opis` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-obrazki-2`
--

CREATE TABLE `gk-obrazki-2` (
  `typ` int(11) DEFAULT NULL,
  `obrazekid` bigint(20) NOT NULL,
  `id` bigint(20) NOT NULL,
  `id_kreta` bigint(20) NOT NULL,
  `user` bigint(20) NOT NULL,
  `plik` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `opis` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-obserwable`
--

CREATE TABLE `gk-obserwable` (
  `userid` int(10) UNSIGNED NOT NULL,
  `id` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-ostatnieruchy`
--

CREATE TABLE `gk-ostatnieruchy` (
  `ruch_id` int(10) UNSIGNED DEFAULT '0',
  `id` int(10) UNSIGNED,
  `lat` double(8,5) DEFAULT NULL,
  `lon` double(8,5) DEFAULT NULL,
  `alt` int(5) DEFAULT '-32768',
  `country` varchar(3) CHARACTER SET utf8 COLLATE utf8_polish_ci,
  `droga` int(10) UNSIGNED,
  `waypoint` varchar(10) CHARACTER SET utf8 COLLATE utf8_polish_ci,
  `data` datetime DEFAULT NULL,
  `data_dodania` datetime DEFAULT NULL,
  `user` int(10) UNSIGNED DEFAULT '0',
  `koment` varchar(5120) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL,
  `zdjecia` tinyint(3) UNSIGNED DEFAULT '0',
  `komentarze` smallint(5) UNSIGNED DEFAULT '0',
  `logtype` enum('0','1','2','3','4','5','6') CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT '0' COMMENT '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip',
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_polish_ci,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `app` varchar(16) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT 'www' COMMENT 'source of the log',
  `app_ver` varchar(16) CHARACTER SET utf8 COLLATE utf8_polish_ci COMMENT 'apploction version/codename'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `gk-owner-codes`
--

CREATE TABLE `gk-owner-codes` (
  `id` int(10) UNSIGNED NOT NULL,
  `kret_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(20) COLLATE utf8_polish_ci NOT NULL,
  `generated_date` datetime NOT NULL,
  `claimed_date` datetime NOT NULL,
  `user_id` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-races`
--

CREATE TABLE `gk-races` (
  `raceid` bigint(20) NOT NULL,
  `created` datetime NOT NULL COMMENT 'kiedy utworzono',
  `raceOwner` bigint(20) NOT NULL,
  `private` binary(1) NOT NULL DEFAULT '0' COMMENT '0 = public, 1 = private',
  `haslo` varchar(16) COLLATE utf8_bin NOT NULL COMMENT 'haslo tajnego wyścigu',
  `raceTitle` varchar(32) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `racestart` date NOT NULL COMMENT 'początek rajdu',
  `raceend` date NOT NULL,
  `opis` varchar(5120) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `raceOpts` varchar(16) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL COMMENT 'typ wyścigu',
  `wpt` varchar(16) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `targetlat` double DEFAULT NULL,
  `targetlon` double DEFAULT NULL,
  `targetDist` bigint(20) DEFAULT NULL COMMENT 'docelowa ogległość',
  `targetCaches` bigint(20) DEFAULT NULL COMMENT 'docelowa liczba keszy',
  `status` int(1) NOT NULL COMMENT 'status wyścigu. 0=zapisy, 1=trwa, 2=skończony, 3=zakończony ale logi spływają'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='race definitions';

-- --------------------------------------------------------

--
-- Table structure for table `gk-races-krety`
--

CREATE TABLE `gk-races-krety` (
  `raceGkId` bigint(20) NOT NULL,
  `raceid` bigint(20) NOT NULL,
  `geokretid` bigint(20) NOT NULL,
  `initDist` bigint(20) NOT NULL COMMENT 'początkowy dystans kreta',
  `initCaches` bigint(20) NOT NULL COMMENT 'początkowa liczba zaliczonych keszy kreta',
  `distToDest` float DEFAULT NULL COMMENT 'dystans do celu',
  `joined` datetime NOT NULL,
  `finished` datetime DEFAULT NULL,
  `finishDist` bigint(20) NOT NULL COMMENT 'dist na chwilę zakończenia rajdu',
  `finishCaches` bigint(20) NOT NULL COMMENT 'caches na chwilę zakończenia rajdu',
  `finishLat` double DEFAULT NULL,
  `finishLon` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci COMMENT='uczestnicy rajdów';

-- --------------------------------------------------------

--
-- Table structure for table `gk-ruchy`
--

CREATE TABLE `gk-ruchy` (
  `ruch_id` int(10) UNSIGNED NOT NULL,
  `id` int(10) UNSIGNED NOT NULL,
  `lat` double(8,5) DEFAULT NULL,
  `lon` double(8,5) DEFAULT NULL,
  `alt` int(5) NOT NULL DEFAULT '-32768',
  `country` varchar(3) COLLATE utf8_polish_ci NOT NULL,
  `droga` int(10) UNSIGNED NOT NULL,
  `waypoint` varchar(10) COLLATE utf8_polish_ci NOT NULL,
  `data` datetime DEFAULT NULL,
  `data_dodania` datetime DEFAULT NULL,
  `user` int(10) UNSIGNED DEFAULT '0',
  `koment` varchar(5120) COLLATE utf8_polish_ci DEFAULT NULL,
  `zdjecia` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `komentarze` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `logtype` enum('0','1','2','3','4','5','6') COLLATE utf8_polish_ci DEFAULT '0' COMMENT '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip',
  `username` varchar(20) COLLATE utf8_polish_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `app` varchar(16) COLLATE utf8_polish_ci NOT NULL DEFAULT 'www' COMMENT 'source of the log',
  `app_ver` varchar(16) COLLATE utf8_polish_ci NOT NULL COMMENT 'apploction version/codename'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-ruchy-comments`
--

CREATE TABLE `gk-ruchy-comments` (
  `comment_id` int(10) UNSIGNED NOT NULL,
  `ruch_id` int(10) UNSIGNED NOT NULL,
  `kret_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'id autora postu',
  `data_dodania` datetime NOT NULL,
  `comment` varchar(500) COLLATE utf8_polish_ci NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL COMMENT '0-sam wpis, 1-brak kreta',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-statystyki-dzienne`
--

CREATE TABLE `gk-statystyki-dzienne` (
  `data` date NOT NULL,
  `unix_timestamp` int(11) NOT NULL,
  `dzien` int(11) NOT NULL,
  `gk` int(11) NOT NULL,
  `gk_` int(11) NOT NULL,
  `gk_zakopane_` int(11) NOT NULL,
  `procent_zakopanych` float NOT NULL,
  `users` int(11) NOT NULL,
  `users_` int(11) NOT NULL,
  `ruchow` int(11) NOT NULL,
  `ruchow_` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci COMMENT='informacje nt. przyrostu zmiennych dzień po dniu';

-- --------------------------------------------------------

--
-- Table structure for table `gk-users`
--

CREATE TABLE `gk-users` (
  `userid` int(10) UNSIGNED NOT NULL,
  `user` varchar(80) COLLATE utf8_polish_ci DEFAULT NULL,
  `haslo` varchar(500) COLLATE utf8_polish_ci DEFAULT NULL,
  `haslo2` varchar(120) COLLATE utf8_polish_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_polish_ci NOT NULL,
  `email_invalid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '* 0 ok * 1 blocked * 2 autoresponder',
  `joined` datetime DEFAULT NULL,
  `wysylacmaile` binary(1) NOT NULL DEFAULT '1',
  `ip` varchar(16) COLLATE utf8_polish_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lang` varchar(2) COLLATE utf8_polish_ci DEFAULT NULL,
  `lat` double(8,5) DEFAULT NULL,
  `lon` double(8,5) DEFAULT NULL,
  `promien` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `country` char(3) COLLATE utf8_polish_ci DEFAULT NULL,
  `godzina` int(11) NOT NULL,
  `statpic` tinyint(1) DEFAULT '1',
  `ostatni_mail` datetime DEFAULT NULL,
  `ostatni_login` datetime NOT NULL,
  `secid` varchar(128) COLLATE utf8_polish_ci NOT NULL COMMENT 'tajny klucz usera'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-wartosci`
--

CREATE TABLE `gk-wartosci` (
  `name` varchar(32) COLLATE utf8_polish_ci NOT NULL,
  `value` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-waypointy`
--

CREATE TABLE `gk-waypointy` (
  `waypoint` varchar(11) COLLATE utf8_polish_ci NOT NULL DEFAULT '',
  `lat` double(8,5) NOT NULL,
  `lon` double(8,5) NOT NULL,
  `alt` int(5) NOT NULL DEFAULT '-32768',
  `country` char(3) COLLATE utf8_polish_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_polish_ci NOT NULL,
  `owner` varchar(150) COLLATE utf8_polish_ci NOT NULL,
  `typ` varchar(200) COLLATE utf8_polish_ci NOT NULL,
  `kraj` varchar(200) COLLATE utf8_polish_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_polish_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gk-waypointy-gc`
--

CREATE TABLE `gk-waypointy-gc` (
  `wpt` varchar(8) COLLATE utf8_polish_ci NOT NULL,
  `lat` float NOT NULL,
  `lon` float NOT NULL,
  `country` varchar(3) COLLATE utf8_polish_ci NOT NULL,
  `alt` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci COMMENT='GC.com waypoints';

-- --------------------------------------------------------

--
-- Table structure for table `gk_simplepo_catalogues`
--

CREATE TABLE `gk_simplepo_catalogues` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gk_simplepo_messages`
--

CREATE TABLE `gk_simplepo_messages` (
  `id` int(11) NOT NULL,
  `catalogue_id` int(11) NOT NULL,
  `msgid` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `msgstr` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `comments` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `extracted_comments` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `reference` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `flags` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `is_obsolete` tinyint(1) NOT NULL,
  `is_header` tinyint(1) NOT NULL,
  `previous_untranslated_string` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `captcha_codes`
--
ALTER TABLE `captcha_codes`
  ADD PRIMARY KEY (`id`,`namespace`),
  ADD KEY `created` (`created`);

--
-- Indexes for table `gk-aktywnesesje`
--
ALTER TABLE `gk-aktywnesesje`
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `gk-badges`
--
ALTER TABLE `gk-badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `gk-errory`
--
ALTER TABLE `gk-errory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`date`),
  ADD KEY `severity` (`severity`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `gk-geokrety`
--
ALTER TABLE `gk-geokrety`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`nr`) USING BTREE,
  ADD KEY `owner` (`owner`),
  ADD KEY `nr` (`nr`),
  ADD KEY `ost_pozycja_id` (`ost_pozycja_id`),
  ADD KEY `avatarid` (`avatarid`),
  ADD KEY `ost_log_id` (`ost_log_id`),
  ADD KEY `hands_of_index` (`hands_of`),
  ADD KEY `id_typ` (`typ`) USING BTREE;

--
-- Indexes for table `gk-geokrety-rating`
--
ALTER TABLE `gk-geokrety-rating`
  ADD PRIMARY KEY (`id`,`userid`);

--
-- Indexes for table `gk-grupy-desc`
--
ALTER TABLE `gk-grupy-desc`
  ADD UNIQUE KEY `groupid` (`groupid`),
  ADD UNIQUE KEY `groupid_2` (`groupid`);

--
-- Indexes for table `gk-licznik`
--
ALTER TABLE `gk-licznik`
  ADD PRIMARY KEY (`witryna`);

--
-- Indexes for table `gk-maile`
--
ALTER TABLE `gk-maile`
  ADD UNIQUE KEY `id_maila` (`id_maila`);

--
-- Indexes for table `gk-miasta`
--
ALTER TABLE `gk-miasta`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `gk-news`
--
ALTER TABLE `gk-news`
  ADD PRIMARY KEY (`news_id`),
  ADD KEY `date` (`date`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `gk-news-comments`
--
ALTER TABLE `gk-news-comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `gk-news-comments-access`
--
ALTER TABLE `gk-news-comments-access`
  ADD PRIMARY KEY (`news_id`,`user_id`);

--
-- Indexes for table `gk-obrazki`
--
ALTER TABLE `gk-obrazki`
  ADD UNIQUE KEY `obrazekid` (`obrazekid`),
  ADD KEY `idkreta_typ` (`id_kreta`,`typ`),
  ADD KEY `id` (`id`),
  ADD KEY `id_kreta` (`id_kreta`);

--
-- Indexes for table `gk-obrazki-2`
--
ALTER TABLE `gk-obrazki-2`
  ADD UNIQUE KEY `obrazekid` (`obrazekid`),
  ADD KEY `idkreta_typ` (`id_kreta`,`typ`);

--
-- Indexes for table `gk-obserwable`
--
ALTER TABLE `gk-obserwable`
  ADD KEY `userid` (`userid`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `gk-owner-codes`
--
ALTER TABLE `gk-owner-codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kret_id` (`kret_id`),
  ADD KEY `code` (`code`);

--
-- Indexes for table `gk-races`
--
ALTER TABLE `gk-races`
  ADD PRIMARY KEY (`raceid`);

--
-- Indexes for table `gk-races-krety`
--
ALTER TABLE `gk-races-krety`
  ADD UNIQUE KEY `raceGkId` (`raceGkId`);

--
-- Indexes for table `gk-ruchy`
--
ALTER TABLE `gk-ruchy`
  ADD PRIMARY KEY (`ruch_id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `waypoint` (`waypoint`),
  ADD KEY `user` (`user`),
  ADD KEY `lat` (`lat`),
  ADD KEY `lon` (`lon`),
  ADD KEY `logtype` (`logtype`),
  ADD KEY `data` (`data`),
  ADD KEY `data_dodania` (`data_dodania`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `alt` (`alt`);

--
-- Indexes for table `gk-ruchy-comments`
--
ALTER TABLE `gk-ruchy-comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `kret_id` (`kret_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ruch_id` (`ruch_id`);

--
-- Indexes for table `gk-statystyki-dzienne`
--
ALTER TABLE `gk-statystyki-dzienne`
  ADD UNIQUE KEY `data` (`data`);

--
-- Indexes for table `gk-users`
--
ALTER TABLE `gk-users`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `user` (`user`),
  ADD KEY `secid` (`secid`),
  ADD KEY `ostatni_login` (`ostatni_login`),
  ADD KEY `email_invalid` (`email_invalid`);

--
-- Indexes for table `gk-wartosci`
--
ALTER TABLE `gk-wartosci`
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `gk-waypointy`
--
ALTER TABLE `gk-waypointy`
  ADD PRIMARY KEY (`waypoint`),
  ADD UNIQUE KEY `waypoint` (`waypoint`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `gk-waypointy-gc`
--
ALTER TABLE `gk-waypointy-gc`
  ADD UNIQUE KEY `wpt` (`wpt`);

--
-- Indexes for table `gk_simplepo_catalogues`
--
ALTER TABLE `gk_simplepo_catalogues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `gk_simplepo_messages`
--
ALTER TABLE `gk_simplepo_messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gk-badges`
--
ALTER TABLE `gk-badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=948;

--
-- AUTO_INCREMENT for table `gk-errory`
--
ALTER TABLE `gk-errory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1208370;

--
-- AUTO_INCREMENT for table `gk-geokrety`
--
ALTER TABLE `gk-geokrety`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67914;

--
-- AUTO_INCREMENT for table `gk-grupy-desc`
--
ALTER TABLE `gk-grupy-desc`
  MODIFY `groupid` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `gk-maile`
--
ALTER TABLE `gk-maile`
  MODIFY `id_maila` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6835;

--
-- AUTO_INCREMENT for table `gk-news`
--
ALTER TABLE `gk-news`
  MODIFY `news_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `gk-news-comments`
--
ALTER TABLE `gk-news-comments`
  MODIFY `comment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `gk-obrazki`
--
ALTER TABLE `gk-obrazki`
  MODIFY `obrazekid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53679;

--
-- AUTO_INCREMENT for table `gk-obrazki-2`
--
ALTER TABLE `gk-obrazki-2`
  MODIFY `obrazekid` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5147;

--
-- AUTO_INCREMENT for table `gk-owner-codes`
--
ALTER TABLE `gk-owner-codes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `gk-races`
--
ALTER TABLE `gk-races`
  MODIFY `raceid` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `gk-races-krety`
--
ALTER TABLE `gk-races-krety`
  MODIFY `raceGkId` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `gk-ruchy`
--
ALTER TABLE `gk-ruchy`
  MODIFY `ruch_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1349547;

--
-- AUTO_INCREMENT for table `gk-ruchy-comments`
--
ALTER TABLE `gk-ruchy-comments`
  MODIFY `comment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15260;

--
-- AUTO_INCREMENT for table `gk-users`
--
ALTER TABLE `gk-users`
  MODIFY `userid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38785;

--
-- AUTO_INCREMENT for table `gk_simplepo_catalogues`
--
ALTER TABLE `gk_simplepo_catalogues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `gk_simplepo_messages`
--
ALTER TABLE `gk_simplepo_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83584;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
