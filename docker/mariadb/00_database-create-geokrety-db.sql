-- Adminer 4.7.3 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

CREATE TABLE `gk-account-activation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=unused 1=validated 2=expired',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_on_datetime` datetime DEFAULT NULL,
  `requesting_ip` varchar(46) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validating_ip` varchar(46) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `used_created_on_datetime` (`used`,`created_on_datetime`),
  CONSTRAINT `gk-account-activation_ibfk_1` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-activation-codes` (
  `token` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holder` int(11) unsigned NOT NULL,
  `description` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `awarded_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`awarded_on_datetime`),
  KEY `userid` (`holder`),
  CONSTRAINT `gk-badges_ibfk_1` FOREIGN KEY (`holder`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='badges for the users';


CREATE TABLE `gk-email-activation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revert_token` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `previous_email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Store the previous in case of needed rollback',
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `used` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=unused 1=validated 2=refused 3=expired',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `used_on_datetime` datetime DEFAULT NULL,
  `reverted_on_datetime` datetime DEFAULT NULL,
  `requesting_ip` varchar(46) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updating_ip` varchar(46) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reverting_ip` varchar(46) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `token` (`token`),
  KEY `used_created_on_datetime_token` (`used`,`created_on_datetime`,`token`),
  KEY `used_used_on_datetime_revert_token` (`used`,`used_on_datetime`,`revert_token`),
  KEY `used_created_on_datetime_used_on_datetime` (`used`,`created_on_datetime`,`used_on_datetime`),
  CONSTRAINT `gk-email-activation_ibfk_1` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `gk-email-activation_ibfk_2` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-geokrety` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gkid` int(11) unsigned NOT NULL COMMENT 'The real GK id : https://stackoverflow.com/a/33791018/944936',
  `tracking_code` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mission` mediumtext COLLATE utf8mb4_unicode_ci,
  `owner` int(11) unsigned DEFAULT NULL,
  `distance` int(10) unsigned NOT NULL DEFAULT '0',
  `caches_count` smallint(6) NOT NULL DEFAULT '0',
  `pictures_count` smallint(6) NOT NULL DEFAULT '0',
  `last_position` int(11) unsigned DEFAULT NULL,
  `last_log` int(11) unsigned DEFAULT NULL,
  `holder` int(11) unsigned DEFAULT NULL COMMENT 'In the hands of user',
  `missing` tinyint(4) NOT NULL DEFAULT '0',
  `type` enum('0','1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` int(10) unsigned DEFAULT NULL,
  `timestamp_oc` datetime NOT NULL COMMENT 'Unused?',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`tracking_code`),
  KEY `owner` (`owner`),
  KEY `nr` (`tracking_code`),
  KEY `ost_pozycja_id` (`last_position`),
  KEY `avatarid` (`avatar`),
  KEY `ost_log_id` (`last_log`),
  KEY `hands_of_index` (`holder`),
  KEY `id_typ` (`type`),
  CONSTRAINT `gk-geokrety_ibfk_1` FOREIGN KEY (`holder`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-geokrety_ibfk_2` FOREIGN KEY (`owner`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-geokrety-rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `geokret` int(11) unsigned NOT NULL COMMENT 'id kreta',
  `user` int(11) unsigned NOT NULL COMMENT 'id usera',
  `rate` double NOT NULL DEFAULT '0' COMMENT 'single rating (number of stars)',
  `rated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `geokret` (`geokret`),
  KEY `user` (`user`),
  CONSTRAINT `gk-geokrety-rating_ibfk_1` FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-geokrety-rating_ibfk_2` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='GK ratings';


CREATE TABLE `gk-mail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` int(11) unsigned NOT NULL,
  `to` int(11) unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(46) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_maila` (`id`),
  KEY `from` (`from`),
  KEY `to` (`to`),
  CONSTRAINT `gk-mail_ibfk_1` FOREIGN KEY (`from`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-mail_ibfk_2` FOREIGN KEY (`to`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-move-comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `move` int(11) unsigned NOT NULL,
  `geokret` int(11) unsigned NOT NULL,
  `author` int(11) unsigned NOT NULL,
  `content` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '0=comment, 1=missing',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `kret_id` (`geokret`),
  KEY `user_id` (`author`),
  KEY `ruch_id` (`move`),
  CONSTRAINT `gk-move-comments_ibfk_1` FOREIGN KEY (`move`) REFERENCES `gk-moves` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `gk-move-comments_ibfk_2` FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `gk-move-comments_ibfk_3` FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-moves` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `geokret` int(11) unsigned NOT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `alt` int(5) DEFAULT '-32768',
  `country` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ISO 3166-1 https://fr.wikipedia.org/wiki/ISO_3166-1',
  `distance` int(10) unsigned DEFAULT NULL,
  `waypoint` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` int(11) unsigned DEFAULT '0',
  `comment` varchar(5120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pictures_count` tinyint(4) DEFAULT '0',
  `comments_count` smallint(6) DEFAULT '0',
  `logtype` enum('0','1','2','3','4','5','6') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'source of the log',
  `app_ver` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'application version/codename',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moved_on_datetime` datetime NOT NULL COMMENT 'The move as configured by user',
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_2` (`geokret`),
  KEY `waypoint` (`waypoint`),
  KEY `user` (`author`),
  KEY `lat` (`lat`),
  KEY `lon` (`lon`),
  KEY `logtype` (`logtype`),
  KEY `data` (`created_on_datetime`),
  KEY `data_dodania` (`moved_on_datetime`),
  KEY `timestamp` (`updated_on_datetime`),
  KEY `alt` (`alt`),
  CONSTRAINT `gk-moves_ibfk_1` FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-moves_ibfk_2` FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `author_name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` int(11) unsigned DEFAULT NULL,
  `comments_count` smallint(6) NOT NULL DEFAULT '0',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_commented_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date` (`created_on_datetime`),
  KEY `userid` (`author`),
  CONSTRAINT `gk-news_ibfk_1` FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-news_ibfk_2` FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-news-comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `news` int(11) unsigned NOT NULL,
  `author` int(11) unsigned NOT NULL,
  `content` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` tinyint(4) NOT NULL,
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `news` (`news`),
  KEY `author` (`author`),
  CONSTRAINT `gk-news-comments_ibfk_1` FOREIGN KEY (`news`) REFERENCES `gk-news` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-news-comments_ibfk_2` FOREIGN KEY (`news`) REFERENCES `gk-news` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-news-comments_ibfk_3` FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-news-comments-access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news` int(11) unsigned NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `last_read_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_post_datetime` datetime DEFAULT NULL,
  `subscribed` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`news`,`user`),
  UNIQUE KEY `id` (`id`),
  KEY `user` (`user`),
  CONSTRAINT `gk-news-comments-access_ibfk_1` FOREIGN KEY (`news`) REFERENCES `gk-news` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-news-comments-access_ibfk_2` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-owner-codes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `geokret` int(11) unsigned NOT NULL,
  `token` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `claimed_on_datetime` datetime DEFAULT NULL,
  `user` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kret_id` (`geokret`),
  KEY `code` (`token`),
  KEY `user` (`user`),
  CONSTRAINT `gk-owner-codes_ibfk_1` FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-owner-codes_ibfk_2` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-owner-codes_ibfk_3` FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `gk-owner-codes_ibfk_4` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-password-tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=unused 1=used',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_on_datetime` datetime DEFAULT NULL,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `requesting_ip` varchar(46) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `token_used` (`token`,`used`),
  KEY `created_on_datetime` (`created_on_datetime`),
  CONSTRAINT `gk-password-tokens_ibfk_1` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='Retrieve user password';


CREATE TABLE `gk-pictures` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `typ` tinyint(4) DEFAULT NULL,
  `move` int(11) unsigned DEFAULT NULL,
  `geokret` int(11) unsigned DEFAULT NULL,
  `user` int(11) unsigned DEFAULT NULL,
  `filename` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `obrazekid` (`id`),
  KEY `idkreta_typ` (`geokret`,`typ`),
  KEY `id` (`move`),
  KEY `id_kreta` (`geokret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-races` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation date',
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `organizer` int(11) unsigned NOT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = public, 1 = private',
  `password` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'password to join the race',
  `title` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(5120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_on_datetime` datetime DEFAULT NULL COMMENT 'Race start date',
  `end_on_datetime` datetime DEFAULT NULL COMMENT 'Race end date',
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of race',
  `waypoint` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_lat` double DEFAULT NULL,
  `target_lon` double DEFAULT NULL,
  `target_dist` int(11) DEFAULT NULL COMMENT 'target distance',
  `target_caches` int(11) DEFAULT NULL COMMENT 'targeted number of caches',
  `status` int(1) NOT NULL COMMENT 'race status. 0 = initialized, 1 = persist, 2 = finite, 3 = finished but logs flow down',
  PRIMARY KEY (`id`),
  KEY `organizer` (`organizer`),
  CONSTRAINT `gk-races_ibfk_1` FOREIGN KEY (`organizer`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='race definitions';


CREATE TABLE `gk-races-krety` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `race` int(11) NOT NULL,
  `geokret` int(11) unsigned NOT NULL,
  `initial_distance` int(11) NOT NULL,
  `initial_caches_count` int(11) NOT NULL,
  `distance_to_destination` double DEFAULT NULL,
  `joined_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `finished_on_datetime` datetime DEFAULT NULL,
  `finish_distance` int(11) NOT NULL,
  `finish_caches_count` int(11) NOT NULL,
  `finish_lat` double DEFAULT NULL,
  `finish_lon` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `raceGkId` (`id`),
  KEY `race` (`race`),
  KEY `geokret` (`geokret`),
  CONSTRAINT `gk-races-krety_ibfk_1` FOREIGN KEY (`race`) REFERENCES `gk-races` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-races-krety_ibfk_2` FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='uczestnicy rajdów';


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
  PRIMARY KEY (`data`),
  UNIQUE KEY `data` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='informacje nt. przyrostu zmiennych dzień po dniu';


CREATE TABLE `gk-users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `old_password` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'This hash is not used anymore',
  `password` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_valid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=unconfirmed 1=confirmed',
  `email_invalid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '* 0 ok * 1 blocked * 2 autoresponder',
  `joined_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `daily_mails` tinyint(1) NOT NULL DEFAULT '1',
  `registration_ip` varchar(46) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preferred_language` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_latitude` double DEFAULT NULL,
  `home_longitude` double DEFAULT NULL,
  `observation_area` smallint(6) DEFAULT NULL,
  `home_country` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `daily_mails_hour` int(11) NOT NULL,
  `statpic_template_id` tinyint(1) NOT NULL DEFAULT '1',
  `last_mail_datetime` datetime DEFAULT NULL,
  `last_login_datetime` datetime DEFAULT NULL,
  `terms_of_use_datetime` datetime NOT NULL COMMENT 'Acceptation date',
  `secid` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'connect by other applications',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`username`),
  KEY `secid` (`secid`),
  KEY `ostatni_login` (`last_login_datetime`),
  KEY `email_invalid` (`email_invalid`),
  KEY `email` (`email`),
  KEY `username` (`username`),
  KEY `username_email` (`username`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


CREATE TABLE `gk-wartosci` (
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`name`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

INSERT INTO `gk-wartosci` (`name`, `value`) VALUES
('droga_mediana',	0),
('droga_srednia',	0),
('stat_droga',	0),
('stat_droga_ksiezyc',	0),
('stat_droga_obwod',	0),
('stat_droga_slonce',	0),
('stat_geokretow',	0),
('stat_geokretow_zakopanych',	0),
('stat_ruchow',	0),
('stat_userow',	0);

CREATE TABLE `gk-watched` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) unsigned NOT NULL,
  `geokret` int(11) unsigned NOT NULL,
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userid` (`user`),
  KEY `id` (`geokret`),
  CONSTRAINT `gk-watched_ibfk_1` FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gk-watched_ibfk_2` FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-waypointy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `waypoint` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `alt` int(5) NOT NULL DEFAULT '-32768',
  `country` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'country code as ISO 3166-1 alpha-2',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'full English country name',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `added_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `waypoint` (`waypoint`),
  KEY `waypoint_2` (`waypoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


CREATE TABLE `gk-waypointy-country` (
  `kraj` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`kraj`),
  UNIQUE KEY `unique_kraj` (`kraj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

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

CREATE TABLE `gk-waypointy-sync` (
  `service_id` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='Last synchronization time for GC services';


CREATE TABLE `gk-waypointy-type` (
  `typ` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cache_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`typ`),
  UNIQUE KEY `unique_typ` (`typ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

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

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20190925191013,	NULL,	NULL,	NULL,	0);

-- 2019-09-25 20:36:36
