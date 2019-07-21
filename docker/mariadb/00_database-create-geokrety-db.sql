-- Adminer 4.7.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

CREATE TABLE `captcha_codes` (
  `id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `namespace` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_display` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` int(11) NOT NULL,
  `audio_data` mediumblob,
  PRIMARY KEY (`id`,`namespace`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-aktywnekody` (
  `kod` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-aktywnemaile` (
  `kod` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userid` bigint(20) NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `done` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1=confirmed',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-aktywnesesje` (
  `sessid` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userid` bigint(20) NOT NULL,
  `user` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember` binary(1) NOT NULL DEFAULT '0',
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `desc` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='badges for the users';


CREATE TABLE `gk-errory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `userid` int(10) unsigned NOT NULL,
  `ip` varchar(46) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` int(10) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `severity` (`severity`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-geokrety` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nr` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nazwa` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opis` mediumtext COLLATE utf8mb4_unicode_ci,
  `owner` int(10) unsigned DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `droga` int(10) unsigned NOT NULL,
  `skrzynki` smallint(5) unsigned NOT NULL,
  `zdjecia` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ost_pozycja_id` int(10) unsigned NOT NULL,
  `ost_log_id` int(10) unsigned NOT NULL,
  `hands_of` int(10) unsigned DEFAULT NULL COMMENT 'In the hands of user',
  `missing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `typ` enum('0','1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatarid` int(10) unsigned NOT NULL,
  `timestamp_oc` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`nr`) USING BTREE,
  KEY `owner` (`owner`),
  KEY `nr` (`nr`),
  KEY `ost_pozycja_id` (`ost_pozycja_id`),
  KEY `avatarid` (`avatarid`),
  KEY `ost_log_id` (`ost_log_id`),
  KEY `hands_of_index` (`hands_of`),
  KEY `id_typ` (`typ`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-geokrety-rating` (
  `id` bigint(20) NOT NULL COMMENT 'id kreta',
  `userid` bigint(20) NOT NULL COMMENT 'id usera',
  `rate` float NOT NULL DEFAULT '0' COMMENT 'single rating (number of stars)',
  PRIMARY KEY (`id`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='GK ratings';


CREATE TABLE `gk-grupy` (
  `groupid` bigint(20) NOT NULL,
  `kretid` bigint(20) NOT NULL,
  `joined` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='wchich kret belongs to which group';


CREATE TABLE `gk-grupy-desc` (
  `groupid` bigint(20) NOT NULL AUTO_INCREMENT,
  `creator` bigint(20) NOT NULL,
  `created` datetime DEFAULT NULL,
  `private` binary(1) NOT NULL,
  `desc` blob NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `groupid` (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='descriptions of groups';


CREATE TABLE `gk-licznik` (
  `witryna` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `licznik` bigint(20) NOT NULL,
  `od` datetime NOT NULL,
  PRIMARY KEY (`witryna`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-maile` (
  `id_maila` bigint(20) NOT NULL AUTO_INCREMENT,
  `random_string` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` bigint(20) NOT NULL,
  `to` bigint(20) NOT NULL,
  `temat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tresc` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `id_maila` (`id_maila`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-miasta` (
  `id` bigint(20) NOT NULL,
  `name` varchar(130) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asciiname` varchar(130) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alternatenames` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `country` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-news` (
  `news_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `czas_postu` datetime NOT NULL,
  `tytul` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tresc` longtext COLLATE utf8mb4_unicode_ci,
  `who` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userid` int(10) NOT NULL,
  `komentarze` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ostatni_komentarz` datetime DEFAULT NULL,
  PRIMARY KEY (`news_id`),
  KEY `date` (`date`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-news-comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `news_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `comment` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-news-comments-access` (
  `news_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `read` datetime DEFAULT NULL,
  `post` datetime DEFAULT NULL,
  `subscribed` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`news_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-obrazki` (
  `typ` tinyint(3) unsigned DEFAULT NULL,
  `obrazekid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned NOT NULL,
  `id_kreta` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `plik` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `obrazekid` (`obrazekid`),
  KEY `idkreta_typ` (`id_kreta`,`typ`),
  KEY `id` (`id`),
  KEY `id_kreta` (`id_kreta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-obserwable` (
  `userid` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  KEY `userid` (`userid`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-ostatnieruchy` (
  `ruch_id` int(10) unsigned DEFAULT '0',
  `id` int(10) unsigned DEFAULT NULL,
  `lat` double(8,5) DEFAULT NULL,
  `lon` double(8,5) DEFAULT NULL,
  `alt` int(5) DEFAULT '-32768',
  `country` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `droga` int(10) unsigned DEFAULT NULL,
  `waypoint` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `data_dodania` datetime DEFAULT NULL,
  `user` int(10) unsigned DEFAULT '0',
  `koment` varchar(5120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zdjecia` tinyint(3) unsigned DEFAULT '0',
  `komentarze` smallint(5) unsigned DEFAULT '0',
  `logtype` enum('0','1','2','3','4','5','6') COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `app` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT 'www' COMMENT 'source of the log',
  `app_ver` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'apploction version/codename'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-owner-codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kret_id` int(10) unsigned NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generated_date` datetime NOT NULL,
  `claimed_date` datetime NOT NULL,
  `user_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `kret_id` (`kret_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-races` (
  `raceid` bigint(20) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL COMMENT 'Creation date',
  `raceOwner` bigint(20) NOT NULL,
  `private` binary(1) NOT NULL DEFAULT '0' COMMENT '0 = public, 1 = private',
  `haslo` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'password to join the race',
  `raceTitle` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `racestart` date NOT NULL COMMENT 'Race start date',
  `raceend` date NOT NULL,
  `opis` varchar(5120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `raceOpts` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of race',
  `wpt` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `targetlat` double DEFAULT NULL,
  `targetlon` double DEFAULT NULL,
  `targetDist` bigint(20) DEFAULT NULL COMMENT 'target distance',
  `targetCaches` bigint(20) DEFAULT NULL COMMENT 'targeted number of caches',
  `status` int(1) NOT NULL COMMENT 'race status. 0 = initialized, 1 = persist, 2 = finite, 3 = finished but logs flow down',
  PRIMARY KEY (`raceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='race definitions';


CREATE TABLE `gk-races-krety` (
  `raceGkId` bigint(20) NOT NULL AUTO_INCREMENT,
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
  `finishLon` double DEFAULT NULL,
  UNIQUE KEY `raceGkId` (`raceGkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='uczestnicy rajdów';


CREATE TABLE `gk-ruchy` (
  `ruch_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned NOT NULL,
  `lat` double(8,5) DEFAULT NULL,
  `lon` double(8,5) DEFAULT NULL,
  `alt` int(5) DEFAULT '-32768',
  `country` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `droga` int(10) unsigned NOT NULL DEFAULT '0',
  `waypoint` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `data_dodania` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` int(10) unsigned DEFAULT '0',
  `koment` text COLLATE utf8mb4_unicode_ci,
  `zdjecia` tinyint(3) unsigned DEFAULT '0',
  `komentarze` smallint(5) unsigned DEFAULT '0',
  `logtype` enum('0','1','2','3','4','5','6') COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `app` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'source of the log',
  `app_ver` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'apploction version/codename',
  PRIMARY KEY (`ruch_id`),
  KEY `id_2` (`id`),
  KEY `waypoint` (`waypoint`),
  KEY `user` (`user`),
  KEY `lat` (`lat`),
  KEY `lon` (`lon`),
  KEY `logtype` (`logtype`),
  KEY `data` (`data`),
  KEY `data_dodania` (`data_dodania`),
  KEY `timestamp` (`timestamp`),
  KEY `alt` (`alt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-ruchy-comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ruch_id` int(10) unsigned NOT NULL,
  `kret_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT 'id autora postu',
  `data_dodania` datetime NOT NULL,
  `comment` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '0-sam wpis, 1-brak kreta',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `kret_id` (`kret_id`),
  KEY `user_id` (`user_id`),
  KEY `ruch_id` (`ruch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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
  `ruchow_` int(11) NOT NULL,
  UNIQUE KEY `data` (`data`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='informacje nt. przyrostu zmiennych dzień po dniu';


CREATE TABLE `gk-users` (
  `userid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `haslo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `haslo2` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email_invalid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '* 0 ok * 1 blocked * 2 autoresponder',
  `joined` datetime DEFAULT NULL,
  `wysylacmaile` binary(1) NOT NULL DEFAULT '1',
  `ip` varchar(46) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lang` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` double(8,5) DEFAULT NULL,
  `lon` double(8,5) DEFAULT NULL,
  `promien` smallint(5) unsigned NOT NULL DEFAULT '0',
  `country` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `godzina` int(11) NOT NULL,
  `statpic` tinyint(1) DEFAULT '1',
  `ostatni_mail` datetime DEFAULT NULL,
  `ostatni_login` datetime DEFAULT NULL,
  `secid` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'tajny klucz usera',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `user` (`user`),
  KEY `secid` (`secid`),
  KEY `ostatni_login` (`ostatni_login`),
  KEY `email_invalid` (`email_invalid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `gk-wartosci` (
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` float NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-waypointy` (
  `waypoint` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lat` double(8,5) DEFAULT NULL,
  `lon` double(8,5) DEFAULT NULL,
  `alt` int(5) NOT NULL DEFAULT '-32768',
  `country` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'country code as ISO 3166-1 alpha-2',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `typ` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kraj` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'full English country name',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`waypoint`),
  UNIQUE KEY `waypoint` (`waypoint`),
  KEY `name` (`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `gk-waypointy-country` (
  `kraj` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique_kraj` (`kraj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gk-waypointy-country` (`kraj`, `country`) VALUES
('Afghanistan',	'Afghanistan'),
('Ägypten',	'Egypt'),
('Albania',	'Albania'),
('Albanien',	'Albania'),
('Amerikanisch-Ozeanien',	'American Samoa'),
('Argentinien',	'Argentina'),
('Argentyna',	'Argentina'),
('Armenia',	'Armenia'),
('AT',	'Austria'),
('AU',	'Australia'),
('Australien',	'Australia'),
('Austria',	'Austria'),
('Bahamas',	'Bahamas'),
('Belarus (Weißrußland)',	'Belarus'),
('Belgia',	'Belgium'),
('Belgien',	'Belgium'),
('Białoruś',	'Belarus'),
('Bosnien-Herzegowina',	'Bosnia and Herzegovina'),
('Botsuana',	'Botswana'),
('Brasilien',	'Brazil'),
('Bulgarien',	'Bulgaria'),
('Bułgaria',	'Bulgaria'),
('CA',	'Canada'),
('Canada',	'Canada'),
('Česká Republika',	'Czech Republic'),
('Chile',	'Chile'),
('Chiny',	'China'),
('Chorwacja',	'Croatia'),
('Costa Rica',	'Costa Rica'),
('Cypr',	'Cyprus'),
('CZ',	'Czech Republic'),
('Czeska Republika',	'Czech Republic'),
('Dänemark',	'Denmark'),
('Dania',	'Denmark'),
('DE',	'Germany'),
('Demokratische Volksrepublik Korea',	'North Korea'),
('Denmark',	'Denmark'),
('Deutschland',	'Germany'),
('Dominikanische Republik',	'Dominican Republic'),
('Ecuador',	'Ecuador'),
('Egipt',	'Egypt'),
('El Salvador',	'El Salvador'),
('ES',	'Spain'),
('Estland',	'Estonia'),
('Estonia',	'Estonia'),
('Falklandinseln',	'Falkland Islands'),
('Färöer (zu Dänemark)',	'Faroe Islands'),
('Finlandia',	'Finland'),
('Finnland',	'Finland'),
('France',	'France'),
('Francja',	'France'),
('Frankreich',	'France'),
('Georgien',	'Georgia'),
('Germany',	'Germany'),
('Gibraltar',	'Gibraltar'),
('Granada',	'Granada'),
('Grecja',	'Greece'),
('Greenland',	'Greenland'),
('Griechenland',	'Greece'),
('Grönland',	'Greenland'),
('Großbritannien',	'United Kingdom'),
('Guatemala',	'Guatemala'),
('Hiszpania',	'Spain'),
('Holandia',	'Netherlands'),
('Honduras',	'Honduras'),
('HR',	'Croatia'),
('ID',	'Indonesia'),
('IN',	'India'),
('Indie',	'India'),
('Indien',	'India'),
('Irland',	'Ireland'),
('Irlandia',	'Ireland'),
('Island',	'Iceland'),
('Islandia',	'Iceland'),
('Israel',	'Israel'),
('Italien',	'Italy'),
('Japan',	'Japan'),
('Jemen',	'Yemen'),
('Jordanien',	'Jordan'),
('Kambodża',	'Cambodia'),
('Kanada',	'Canada'),
('Kapverden',	'Cape Verde'),
('Kasachstan',	'Kazakhstan'),
('Kenia',	'Kenya'),
('KG',	'Kyrgyzstan'),
('Kirgistan',	'Kyrgyzstan'),
('Kolumbien',	'Colombia'),
('Kroatien',	'Croatia'),
('Kuba',	'Cuba'),
('Laos',	'Laos'),
('Lettland',	'Latvia'),
('Liechtenstein',	'Liechtenstein'),
('Litauen',	'Lithuania'),
('Litwa',	'Lithuania'),
('Luxemburg',	'Luxembourg'),
('Łotwa',	'Latvia'),
('Malaysia',	'Malaysia'),
('Malediven',	'Maldives'),
('Malta',	'Malta'),
('Marokko',	'Morocco'),
('Maroko',	'Morocco'),
('Mauritius',	'Mauritius'),
('Mazedonien',	'Macedonia'),
('Mexico',	'Mexico'),
('Mexiko',	'Mexico'),
('Mołdawia',	'Moldova'),
('Monako',	'Monaco'),
('Montenegro',	'Montenegro'),
('Namibia',	'Namibia'),
('Nepal',	'Nepal'),
('Neuseeland',	'New Zealand'),
('Nicaragua',	'Nicaragua'),
('Niderlandy',	'Netherlands'),
('Niederlande',	'Netherlands'),
('Niederländische Antillen',	'Netherlands Antilles'),
('Niemcy',	'Germany'),
('NO',	'Norway'),
('Norwegen',	'Norway'),
('Norwegia',	'Norway'),
('Österreich',	'Austria'),
('Panama',	'Panama'),
('Papua-Neuguinea',	'Papua New Guinea'),
('Philippinen',	'Philippines'),
('PL',	'Poland'),
('Polen',	'Poland'),
('Polska',	'Poland'),
('Portugal',	'Portugal'),
('Portugalia',	'Portugal'),
('Rosja',	'Russia'),
('Ruanda',	'Rwanda'),
('Rumänien',	'Romania'),
('Rumunia',	'Romania'),
('Russische Föderation',	'Russia'),
('Sambia',	'Zambia'),
('Saudi-Arabien',	'Saudi Arabia'),
('Schweden',	'Sweden'),
('Schweiz',	'Switzerland'),
('SE',	'Sweden'),
('Serbien',	'Serbia'),
('Serbien und Montenegro',	'Serbia and Montenegro'),
('Seychellen',	'Seychelles'),
('SI',	'Slovenia'),
('Simbabwe',	'Zimbabwe'),
('SK',	'Slovakia'),
('Slowakai',	'Slovakia'),
('Slowenien',	'Slovenia'),
('Słowacja',	'Slovakia'),
('Soviet Union',	'Soviet Union'),
('Spain',	'Spain'),
('Spanien',	'Spain'),
('Sri Lanka',	'Sri Lanka'),
('Südafrika',	'South Africa'),
('Sudan',	'Sudan'),
('Südgeorgien und die Südlichen Sandwichinseln',	'South Georgia and the South Sandwich Islands'),
('Svalbard und Jan Mayen',	'Svalbard and Jan Mayen'),
('Sweden',	'Sweden'),
('Syrien',	'Syria'),
('Szwajcaria',	'Switzerland'),
('Szwecja',	'Sweden'),
('Taiwan',	'Taiwan'),
('Tajlandia',	'Thailand'),
('Tansania',	'Tanzania'),
('Thailand',	'Thailand'),
('Togo',	'Togo'),
('Trinidad und Tobago',	'Trinidad and Tobago'),
('Tschad',	'Chad'),
('Tschechische Republik',	'Czech Republic'),
('Tunesien',	'Tunisia'),
('Tunezja',	'Tunisia'),
('Turcja',	'Turkey'),
('Türkei',	'Turkey'),
('UA',	'Ukraine'),
('Uganda',	'Uganda'),
('UK',	'United Kingdom'),
('Ukraina',	'Ukraine'),
('Ukraine',	'Ukraine'),
('Ungarn',	'Hungary'),
('United States',	'United States'),
('US',	'United States'),
('USA',	'USA'),
('Usbekistan',	'Uzbekistan'),
('Vatikan (Heiliger Stuhl)',	'Holy See'),
('Vereinigte Arabische Emirate',	'United Arab Emirates'),
('Vereinigte Staaten',	'United States'),
('Vietnam',	'Vietnam'),
('Virgin Islands (U.S.)',	'United States Virgin Islands'),
('Volksrepublik China',	'China'),
('Węgry',	'Hungary'),
('Wielka Brytania',	'United Kingdom'),
('Wietnam',	'Vietnam'),
('Włochy',	'Italy'),
('Zjednoczone Emiraty Arabskie',	'United Arab Emirates'),
('Zypern',	'Cyprus');

CREATE TABLE `gk-waypointy-gc` (
  `wpt` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lat` float NOT NULL,
  `lon` float NOT NULL,
  `country` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt` float NOT NULL,
  UNIQUE KEY `wpt` (`wpt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='GC.com waypoints';


CREATE TABLE `gk-waypointy-sync` (
  `service_id` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Last synchronization time for GC services';


CREATE TABLE `gk-waypointy-type` (
  `typ` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cache_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique_typ` (`typ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gk-waypointy-type` (`typ`, `cache_type`) VALUES
('beweglicher Cache',	'Mobile'),
('BIT Cache',	'BIT Cache'),
('Cemetery',	'Cemetery'),
('Drive-In',	'Drive-In'),
('Drive-In-Cache',	'Drive-In'),
('Event',	'Event'),
('Event Cache',	'Event'),
('Event-Cache',	'Event'),
('Geocache',	'Traditional'),
('Geocache|Event Cache',	'Event'),
('Geocache|Multi-cache',	'Multicache'),
('Geocache|Mystery Cache',	'Mystery'),
('Geocache|Traditional Cache',	'Traditional'),
('Geocache|Unknown Cache',	'Unknown cache'),
('Geocache|Virtual Cache',	'Virtual'),
('Geocache|Webcam Cache',	'Webcam'),
('Guest Book',	'Guest Book'),
('Inny typ skrzynki',	'Other'),
('kvíz',	'Quiz'),
('Letterbox',	'Letterbox'),
('Mathe-/Physikcache',	'Math / physics cache'),
('Medical Facility',	'Medical Facility'),
('Mobilna',	'Mobile'),
('Moving',	'Mobile'),
('Moving Cache',	'Mobile'),
('MP3 (Podcache)',	'MP3'),
('Multi',	'Multicache'),
('Multicache',	'Multicache'),
('neznámá',	'Unknown cache'),
('normaler Cache',	'Traditional'),
('Other',	'Other'),
('Own cache',	'Own cache'),
('Podcast cache',	'Podcast cache'),
('Quiz',	'Quiz'),
('Rätselcache',	'Mystery'),
('Skrzynka nietypowa',	'Unusual box'),
('tradiční',	'Traditional'),
('Traditional',	'Traditional'),
('Traditional Cache',	'Traditional'),
('Tradycyjna',	'Traditional'),
('unbekannter Cachetyp',	'Unknown cache'),
('Unknown type',	'Unknown cache'),
('USB (Dead Drop)',	'USB'),
('Virtual',	'Virtual'),
('Virtual Cache',	'Virtual'),
('virtueller Cache',	'Virtual'),
('Webcam',	'Webcam'),
('Webcam Cache',	'Webcam'),
('Webcam-Cache',	'Webcam'),
('Wirtualna',	'Virtual'),
('Wydarzenie',	'Event');

-- 2019-07-21 08:17:51
