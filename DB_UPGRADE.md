

# Migrate schema

#Drop unused tables
```sql
DROP TABLE `gk-obrazki-2`;
DROP TABLE `gk_simplepo_messages`;
DROP TABLE `gk_simplepo_catalogues`;
DROP TABLE `gk-aktywnesesje`;
DROP TABLE `gk-errory`;
DROP TABLE `gk-grupy`;
DROP TABLE `gk-grupy-desc`;
DROP TABLE `gk-licznik`;
DROP TABLE `gk-miasta`;
DROP TABLE `gk-ostatnieruchy`;
DROP TABLE `gk-waypointy-gc`;
DROP TABLE `captcha_codes`;
```

**Disable automatic datetime updates**
```sql
ALTER TABLE `gk-geokrety`
CHANGE `data` `data` datetime NULL AFTER `owner`,
CHANGE `timestamp_oc` `timestamp_oc` datetime NULL AFTER `avatarid`,
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `timestamp_oc`;

ALTER TABLE `gk-aktywnekody`
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `kod`;

ALTER TABLE `gk-aktywnemaile`
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `done`;

ALTER TABLE `gk-badges`
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `userid`;

ALTER TABLE `gk-maile`
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `tresc`;

ALTER TABLE `gk-news`
CHANGE `date` `date` timestamp NULL AFTER `news_id`,
CHANGE `czas_postu` `czas_postu` datetime NULL AFTER `date`;

ALTER TABLE `gk-news-comments`
CHANGE `date` `date` datetime NULL AFTER `user_id`;

ALTER TABLE `gk-obrazki`
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `opis`;

ALTER TABLE `gk-owner-codes`
CHANGE `generated_date` `generated_date` datetime NULL AFTER `code`,
CHANGE `claimed_date` `claimed_date` datetime NULL AFTER `generated_date`;

ALTER TABLE `gk-races`
CHANGE `created` `created` datetime NULL COMMENT 'Creation date' AFTER `raceid`;

ALTER TABLE `gk-races-krety`
CHANGE `joined` `joined` datetime NULL AFTER `distToDest`;

ALTER TABLE `gk-ruchy`
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `username`;

ALTER TABLE `gk-ruchy-comments`
CHANGE `data_dodania` `data_dodania` datetime NULL AFTER `user_id`,
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `type`;

ALTER TABLE `gk-users`
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `ip`;

ALTER TABLE `gk-waypointy`
CHANGE `timestamp` `timestamp` timestamp NULL AFTER `status`;
```

```sql

ALTER TABLE `gk-news`
ENGINE='InnoDB';

ALTER TABLE `gk-news-comments`
ENGINE='InnoDB';

ALTER TABLE `gk-news-comments-access`
ENGINE='InnoDB';

ALTER TABLE `gk-owner-codes`
ENGINE='InnoDB';

ALTER TABLE `gk-statystyki-dzienne`
ENGINE='InnoDB';

ALTER TABLE `gk-aktywnekody`
ENGINE='InnoDB';

ALTER TABLE `gk-aktywnemaile`
ENGINE='InnoDB';

ALTER TABLE `gk-maile`
ENGINE='InnoDB';

ALTER TABLE `gk-obserwable`
ENGINE='InnoDB';
```

#Convert charset to utf8mb4

##Prepare
```sql
ALTER TABLE `gk-waypointy-type`
CHANGE `typ` `typ` varchar(191) CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci' NOT NULL FIRST,
CHANGE `cache_type` `cache_type` varchar(191) CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci' NULL AFTER `typ`;

ALTER TABLE `gk-waypointy-country`
CHANGE `kraj` `kraj` varchar(191) CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci' NOT NULL FIRST,
CHANGE `country` `country` varchar(191) CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci' NULL AFTER `kraj`;
```

##Convert
```sql
ALTER DATABASE `geokrety` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
ALTER TABLE `gk-aktywnekody` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-aktywnemaile` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-badges` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-geokrety` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-geokrety-rating` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-maile` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-news` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-news-comments` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-news-comments-access` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-obrazki` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-obserwable` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-owner-codes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-races` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-races-krety` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-ruchy-comments` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-statystyki-dzienne` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-wartosci` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-waypointy` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-waypointy-country` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-waypointy-type` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `gk-ruchy` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

# Special collation for username, as we have users having the same username, one in ascii and the other with polish characters. Maćko / Macko ; Pająki / Pajaki …
```sql
ALTER TABLE `gk-users`
CHANGE `user` `user` varchar(80) COLLATE 'utf8mb4_polish_ci' NOT NULL AFTER `userid`,
CHANGE `haslo` `haslo` varchar(500) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `user`,
CHANGE `haslo2` `haslo2` varchar(120) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `haslo`,
CHANGE `email` `email` varchar(150) COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT '' AFTER `haslo2`,
CHANGE `ip` `ip` varchar(46) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `wysylacmaile`,
CHANGE `lang` `lang` varchar(2) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `timestamp`,
CHANGE `country` `country` char(3) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `promien`,
CHANGE `secid` `secid` varchar(128) COLLATE 'utf8mb4_unicode_ci' NOT NULL COMMENT 'tajny klucz usera' AFTER `ostatni_login`;
```

```sql
ALTER TABLE `gk-ruchy`
CHANGE `alt` `alt` int(5) NULL DEFAULT '-32768' AFTER `lon`,
CHANGE `country` `country` varchar(3) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `alt`,
CHANGE `droga` `droga` int(10) unsigned NULL AFTER `country`,
CHANGE `waypoint` `waypoint` varchar(10) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `droga`,
CHANGE `zdjecia` `zdjecia` tinyint(3) unsigned NULL DEFAULT '0' AFTER `koment`,
CHANGE `komentarze` `komentarze` smallint(5) unsigned NULL DEFAULT '0' AFTER `zdjecia`,
CHANGE `app` `app` varchar(16) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'source of the log' AFTER `timestamp`,
CHANGE `app_ver` `app_ver` varchar(16) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'application version/codename' AFTER `app`;
```

```sql
ALTER TABLE `gk-waypointy`
CHANGE `country` `country` char(3) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'country code as ISO 3166-1 alpha-2' AFTER `alt`,
CHANGE `name` `name` varchar(255) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `country`,
CHANGE `owner` `owner` varchar(150) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `name`,
CHANGE `typ` `typ` varchar(200) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `owner`,
CHANGE `kraj` `kraj` varchar(200) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'full English country name' AFTER `typ`,
CHANGE `link` `link` varchar(255) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `kraj`;
```

```sql
ALTER TABLE `gk-news`
CHANGE `news_id` `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `gk-news-comments`
CHANGE `comment_id` `id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `news_id` `news` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `user_id` `author` int(10) unsigned NOT NULL AFTER `news`,
CHANGE `date` `updated_on_datetime` datetime NULL AFTER `author`,
CHANGE `comment` `content` varchar(1000) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `updated_on_datetime`;

ALTER TABLE `gk-news-comments`
DROP INDEX `news_id`;
```

```sql
ALTER TABLE `gk-news`
CHANGE `news_id` `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
CHANGE `date` `created_on_datetime` datetime NULL  AFTER `id`,
DROP `czas_postu`,
CHANGE `tytul` `title` varchar(50) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `created_on_datetime`,
CHANGE `tresc` `content` longtext COLLATE 'utf8mb4_unicode_ci' NULL AFTER `title`,
CHANGE `who` `author_name` varchar(80) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `content`,
CHANGE `userid` `author` int(10) unsigned NULL AFTER `author_name`,
CHANGE `komentarze` `comments_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `author`,
CHANGE `ostatni_komentarz` `last_commented_on_datetime` datetime NULL AFTER `comments_count`;

UPDATE `gk-news`
SET author = null
WHERE author=0;


DELETE FROM `gk-news`
WHERE author NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

ALTER TABLE `gk-users`
CHANGE `userid` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `gk-news`
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`);

ALTER TABLE `gk-news`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `gk-news-comments`
CHANGE `news` `news` int(11) unsigned NOT NULL AFTER `id`,
ADD FOREIGN KEY (`news`) REFERENCES `gk-news` (`id`);
```


```sql
ALTER TABLE `gk-news-comments-access`
ADD `id` int NOT NULL AUTO_INCREMENT UNIQUE FIRST;
ALTER TABLE `gk-news-comments-access`
CHANGE `news_id` `news` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `user_id` `user` int(10) unsigned NOT NULL AFTER `news`,
CHANGE `read` `last_read_datetime` datetime NULL AFTER `user`,
CHANGE `post` `last_post_datetime` datetime NULL AFTER `last_read_datetime`;
```

```sql
ALTER TABLE `gk-geokrety`
CHANGE `nr` `tracking_code` varchar(9) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`,
CHANGE `nazwa` `name` varchar(75) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `tracking_code`,
CHANGE `opis` `mission` mediumtext COLLATE 'utf8mb4_unicode_ci' NULL AFTER `name`,
CHANGE `data` `created_on_datetime` datetime NULL  AFTER `owner`,
CHANGE `droga` `distance` int(10) unsigned NOT NULL DEFAULT '0' AFTER `created_on_datetime`,
CHANGE `skrzynki` `caches_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `distance`,
CHANGE `zdjecia` `pictures_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `caches_count`,
CHANGE `ost_pozycja_id` `last_position` int(10) unsigned NULL AFTER `pictures_count`,
CHANGE `ost_log_id` `last_log` int(10) unsigned NULL AFTER `last_position`,
CHANGE `hands_of` `holder` int(10) unsigned NULL COMMENT 'In the hands of user' AFTER `last_log`,
CHANGE `typ` `type` enum('0','1','2','3','4') COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `missing`,
CHANGE `avatarid` `avatar` int(10) unsigned NULL AFTER `type`,
CHANGE `timestamp_oc` `timestamp_oc` datetime NOT NULL COMMENT 'Unused?' AFTER `avatar`,
CHANGE `timestamp` `updated_on_datetime` timestamp NULL AFTER `timestamp_oc`;

UPDATE `gk-geokrety` SET `last_log` = NULL WHERE `last_log` = 0;
UPDATE `gk-geokrety` SET `last_position` = NULL WHERE `last_position` = 0;
UPDATE `gk-geokrety` SET `holder` = NULL WHERE `holder` = 0;
```

**Workaround security issue**
```sql
ALTER TABLE `gk-geokrety`
ADD `gkid` int(10) unsigned NOT NULL COMMENT 'The real GK id : https://stackoverflow.com/a/33791018/944936' AFTER `id`;

update `gk-geokrety`
set gkid=id;

DELIMITER ;;
CREATE TRIGGER `gk-geokrety_gkid` BEFORE INSERT ON `gk-geokrety` FOR EACH ROW
  BEGIN
    SET NEW.gkid= COALESCE((SELECT MAX(gkid) FROM `gk-geokrety`),0) + 1;
  END;;
DELIMITER ;
```

```sql
ALTER TABLE `gk-ruchy`
CHANGE `ruch_id` `id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `id` `geokret` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `country` `country` varchar(3) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'ISO 3166-1 https://fr.wikipedia.org/wiki/ISO_3166-1' AFTER `alt`,
CHANGE `droga` `distance` int(10) unsigned NULL AFTER `country`,
CHANGE `data` `created_on_datetime` datetime NULL AFTER `waypoint`,
CHANGE `data_dodania` `moved_on_datetime` datetime NULL COMMENT 'The move as configured by user' AFTER `created_on_datetime`,
CHANGE `koment` `comment` varchar(5120) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `author`,
CHANGE `zdjecia` `pictures_count` tinyint(3) unsigned NULL DEFAULT '0' AFTER `comment`,
CHANGE `komentarze` `comments_count` smallint(5) unsigned NULL DEFAULT '0' AFTER `pictures_count`,
CHANGE `user` `author` int(10) unsigned NULL DEFAULT '0' AFTER `moved_on_datetime`,
CHANGE `timestamp` `updated_on_datetime` datetime NULL AFTER `username`,
CHANGE `username` `username` varchar(20) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `logtype`;
```

**Fix some weird dates**
```sql
UPDATE `gk-ruchy`
SET created_on_datetime=moved_on_datetime
WHERE created_on_datetime < '2000-01-01 00:00:00'
OR created_on_datetime > '2029-01-01 00:00:00';
```

**Set username as null**
```sql
UPDATE `gk-ruchy`
SET username=null
WHERE username='';
```

**Find rows not having foreign key available and fix them**
```sql
UPDATE `gk-ruchy`
SET author = NULL
WHERE author = 0;

DELETE FROM `gk-ruchy`
WHERE author NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

DELETE FROM `gk-ruchy`
WHERE geokret NOT IN (SELECT DISTINCT id FROM `gk-geokrety`)
LIMIT 50;

DELETE FROM `gk-ruchy`
WHERE geokret = 0;
```

```sql
ALTER TABLE `gk-users`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `user` `username` varchar(80) COLLATE 'utf8mb4_polish_ci' NOT NULL AFTER `id`,
CHANGE `haslo` `old_password` varchar(500) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'This hash is not used anymore' AFTER `username`,
CHANGE `haslo2` `password` varchar(120) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `old_password`,
CHANGE `email` `email` varchar(150) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `password`,
CHANGE `joined` `joined_on_datetime` datetime NULL AFTER `email_invalid`,
CHANGE `wysylacmaile` `daily_mails` tinyint(1) NOT NULL DEFAULT '1' AFTER `joined_on_datetime`,
CHANGE `ip` `registration_ip` varchar(46) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `daily_mails`,
CHANGE `timestamp` `updated_on_datetime` datetime NULL AFTER `registration_ip`,
CHANGE `lang` `preferred_language` varchar(2) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `updated_on_datetime`,
CHANGE `lat` `home_latitude` double(8,5) NULL AFTER `preferred_language`,
CHANGE `lon` `home_longitude` double(8,5) NULL AFTER `home_latitude`,
CHANGE `promien` `observation_area` smallint(5) unsigned NULL AFTER `home_longitude`,
CHANGE `country` `home_country` char(3) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `observation_area`,
CHANGE `godzina` `daily_mails_hour` int(11) NOT NULL AFTER `home_country`,
CHANGE `statpic` `statpic_template_id` tinyint(1) NOT NULL DEFAULT '1' AFTER `daily_mails_hour`,
CHANGE `ostatni_mail` `last_mail_datetime` datetime NULL AFTER `statpic_template_id`,
CHANGE `ostatni_login` `last_login_datetime` datetime NULL AFTER `last_mail_datetime`,
ADD INDEX `email` (`email`);

ALTER TABLE `gk-ruchy`
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`),
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`);
```

```sql
ALTER TABLE `gk-waypointy`
CHANGE `typ` `type` varchar(200) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `owner`,
CHANGE `kraj` `country_name` varchar(200) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'full English country name' AFTER `type`,
CHANGE `timestamp` `updated_on_datetime` datetime NULL AFTER `status`;
ALTER TABLE `gk-waypointy`
ADD `id` int unsigned NOT NULL AUTO_INCREMENT UNIQUE FIRST;
ALTER TABLE `gk-waypointy`
ADD PRIMARY KEY `id` (`id`),
ADD INDEX (`waypoint`),
DROP INDEX `PRIMARY`,
DROP INDEX `id`,
DROP INDEX `name`;
```

**Insert waypoints from moves to waypointy**
```sql
INSERT INTO `gk-waypointy` (waypoint, lat, lon, alt, country, link)
SELECT distinct(replace(`waypoint`, ' ', '')) as waypoint, `lat`, `lon`, `alt`, `country`, concat('https://www.geocaching.com/geocache/', replace(`waypoint`, ' ', ''))
FROM `gk-ruchy`
WHERE waypoint LIKE 'GC%'
ON DUPLICATE KEY UPDATE `gk-waypointy`.waypoint=`gk-ruchy`.waypoint;
```

```sql
ALTER TABLE `gk-ruchy-comments`
CHANGE `comment_id` `id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `ruch_id` `move` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `kret_id` `geokret` int(10) unsigned NOT NULL AFTER `move`,
CHANGE `user_id` `author` int(10) unsigned NOT NULL AFTER `geokret`,
CHANGE `data_dodania` `created_on_datetime` datetime NULL  AFTER `author`,
CHANGE `comment` `content` varchar(500) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `created_on_datetime`,
CHANGE `type` `type` tinyint(3) unsigned NOT NULL COMMENT '0=comment, 1=missing' AFTER `content`,
CHANGE `timestamp` `updated_on_datetime` datetime NULL AFTER `type`,
ADD FOREIGN KEY (`move`) REFERENCES `gk-ruchy` (`id`),
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`),
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`);
```


**Fix gk-badges holders**
```sql
DELETE FROM `gk-badges`
WHERE userid NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;
```

```sql
ALTER TABLE `gk-badges`
CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
CHANGE `userid` `holder` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `timestamp` `awarded_on_datetime` datetime NULL  AFTER `holder`,
CHANGE `desc` `description` varchar(128) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `awarded_on_datetime`,
CHANGE `file` `filename` varchar(32) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `description`,
ADD FOREIGN KEY (`holder`) REFERENCES `gk-users` (`id`);
```

```sql
ALTER TABLE `gk-aktywnemaile`
ADD `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
CHANGE `kod` `token` varchar(60) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`,
CHANGE `userid` `user` int(10) unsigned NOT NULL AFTER `token`,
CHANGE `done` `confirmed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0=unconfirmed 1=confirmed' AFTER `email`,
CHANGE `timestamp` `created_on_datetime` datetime NULL  AFTER `confirmed`,
ADD `requesting_ip` varchar(46) NULL,
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE,
RENAME TO `gk-email-activation`;
```

```sql
ALTER TABLE `gk-maile`
CHANGE `id_maila` `id` bigint(20) NOT NULL AUTO_INCREMENT FIRST,
CHANGE `random_string` `token` varchar(10) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`,
CHANGE `temat` `subject` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `to`,
CHANGE `tresc` `content` mediumtext COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `subject`,
CHANGE `timestamp` `sent_on_datetime` timestamp NULL  ON UPDATE CURRENT_TIMESTAMP AFTER `content`,
CHANGE `ip` `ip` varchar(46) NOT NULL AFTER `sent_on_datetime`,
RENAME TO `gk-mail`;
```

```sql
ALTER TABLE `gk-owner-codes`
CHANGE `kret_id` `geokret` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `code` `token` varchar(20) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `geokret`,
CHANGE `generated_date` `generated_on_datetime` datetime NULL  AFTER `token`,
CHANGE `claimed_date` `claimed_on_datetime` datetime NULL AFTER `generated_on_datetime`,
CHANGE `user_id` `user` int(10) unsigned NULL AFTER `claimed_on_datetime`;

UPDATE `gk-owner-codes` SET `claimed_on_datetime` = NULL, `user` = NULL WHERE `user` = '0';

ALTER TABLE `gk-owner-codes`
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`),
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`);

```

```sql
DELETE FROM `gk-obserwable`
WHERE userid NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

ALTER TABLE `gk-obserwable`
CHANGE `userid` `user` int(11) unsigned NOT NULL FIRST,
CHANGE `id` `geokret` int(11) unsigned NOT NULL AFTER `user`,
RENAME TO `gk-watched`;

ALTER TABLE `gk-watched`
ADD `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`),
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`);
```

```sql
ALTER TABLE `gk-aktywnekody`
ADD `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
CHANGE `kod` `token` varchar(60) COLLATE 'utf8mb4_unicode_ci' NOT NULL FIRST,
CHANGE `timestamp` `created_on_datetime` datetime NULL AFTER `token`,
RENAME TO `gk-activation-codes`;
```

```sql
UPDATE `gk-geokrety`
SET owner = null
WHERE owner = 0;

UPDATE `gk-geokrety`
SET holder = null
WHERE holder = 0;

UPDATE `gk-geokrety`
SET owner=null
WHERE owner NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

SELECT *
FROM `gk-geokrety`
WHERE owner NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

ALTER TABLE `gk-geokrety`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `gkid` `gkid` int(11) unsigned NOT NULL COMMENT 'The real GK id : https://stackoverflow.com/a/33791018/944936' AFTER `id`,
CHANGE `holder` `holder` int(11) unsigned NULL COMMENT 'In the hands of user' AFTER `last_log`,
CHANGE `owner` `owner` int(11) unsigned NULL AFTER `mission`,
ADD FOREIGN KEY (`holder`) REFERENCES `gk-users` (`id`),
ADD FOREIGN KEY (`owner`) REFERENCES `gk-users` (`id`);

ALTER TABLE `gk-ruchy`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `geokret` `geokret` int(11) unsigned NOT NULL AFTER `id`,
CHANGE `author` `author` int(11) unsigned NULL DEFAULT '0' AFTER `moved_on_datetime`;

ALTER TABLE `gk-geokrety-rating`
CHANGE `id` `geokret` int NOT NULL COMMENT 'id kreta' FIRST,
CHANGE `userid` `user` int NOT NULL COMMENT 'id usera' AFTER `geokret`;

ALTER TABLE `gk-geokrety-rating`
DROP INDEX `PRIMARY`;

ALTER TABLE `gk-geokrety-rating`
ADD `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE `gk-geokrety-rating`
CHANGE `geokret` `geokret` int(11) unsigned NOT NULL COMMENT 'id kreta' AFTER `id`,
CHANGE `user` `user` int(11) unsigned NOT NULL COMMENT 'id usera' AFTER `geokret`,
CHANGE `rate` `rate` double NOT NULL DEFAULT '0' COMMENT 'single rating (number of stars)' AFTER `user`,
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`),
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`);

DELETE FROM `gk-mail`
WHERE `from` NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

DELETE FROM `gk-mail`
WHERE `to` NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

ALTER TABLE `gk-mail`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `from` `from` int(11) unsigned NOT NULL AFTER `token`,
CHANGE `to` `to` int(11) unsigned NOT NULL AFTER `from`,
ADD FOREIGN KEY (`from`) REFERENCES `gk-users` (`id`),
ADD FOREIGN KEY (`to`) REFERENCES `gk-users` (`id`);

ALTER TABLE `gk-news`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `author` `author` int(11) unsigned NULL AFTER `author_name`;

ALTER TABLE `gk-news`
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`);

ALTER TABLE `gk-news-comments`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `news` `news` int(11) unsigned NOT NULL AFTER `id`,
CHANGE `author` `author` int(11) unsigned NOT NULL AFTER `news`,
ADD FOREIGN KEY (`news`) REFERENCES `gk-news` (`id`),
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE;

DELETE FROM `gk-news-comments-access`
WHERE `news` NOT IN (SELECT DISTINCT id FROM `gk-news`)
LIMIT 50;

ALTER TABLE `gk-news-comments-access`
CHANGE `news` `news` int(11) unsigned NOT NULL AFTER `id`,
CHANGE `user` `user` int(11) unsigned NOT NULL AFTER `news`,
ADD FOREIGN KEY (`news`) REFERENCES `gk-news` (`id`),
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE;

ALTER TABLE `gk-ruchy`
CHANGE `logtype` `logtype` enum('0','1','2','3','4','5','6') COLLATE 'utf8mb4_unicode_ci' NOT NULL COMMENT '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip' AFTER `comments_count`,
RENAME TO `gk-moves`;

ALTER TABLE `gk-obrazki`
CHANGE `obrazekid` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `typ` `typ` tinyint(3) unsigned NULL AFTER `id`,
CHANGE `id` `move` int(11) unsigned NULL AFTER `typ`,
CHANGE `id_kreta` `geokret` int(11) unsigned NULL AFTER `move`,
CHANGE `user` `user` int(11) unsigned NULL AFTER `geokret`,
CHANGE `plik` `filename` varchar(50) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `user`,
CHANGE `opis` `caption` varchar(50) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `filename`,
CHANGE `timestamp` `created_on_datetime` datetime NULL AFTER `caption`,
ADD `updated_on_datetime` datetime NULL,
RENAME TO `gk-pictures`;

ALTER TABLE `gk-races`
CHANGE `raceid` `id` int NOT NULL AUTO_INCREMENT FIRST,
CHANGE `created` `created_on_datetime` datetime NULL COMMENT 'Creation date' AFTER `id`,
ADD `updated_on_datetime` datetime NULL AFTER `created_on_datetime`,
CHANGE `raceOwner` `organizer` int(11) unsigned NOT NULL AFTER `updated_on_datetime`,
CHANGE `private` `private` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = public, 1 = private' AFTER `organizer`,
CHANGE `haslo` `password` varchar(16) COLLATE 'utf8mb4_unicode_ci' NOT NULL COMMENT 'password to join the race' AFTER `private`,
CHANGE `raceTitle` `title` varchar(32) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `password`,
CHANGE `opis` `description` varchar(5120) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `title`,
CHANGE `racestart` `start_on_datetime` datetime NULL COMMENT 'Race start date' AFTER `description`,
CHANGE `raceend` `end_on_datetime` datetime NULL COMMENT 'Race end date' AFTER `start_on_datetime`,
CHANGE `raceOpts` `type` varchar(16) NOT NULL COMMENT 'Type of race' AFTER `end_on_datetime`,
CHANGE `wpt` `waypoint` varchar(16) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `type`,
CHANGE `targetlat` `target_lat` double NULL AFTER `waypoint`,
CHANGE `targetlon` `target_lon` double NULL AFTER `target_lat`,
CHANGE `targetDist` `target_dist` int(11) NULL COMMENT 'target distance' AFTER `target_lon`,
CHANGE `targetCaches` `target_caches` int(11) NULL COMMENT 'targeted number of caches' AFTER `target_dist`,
ADD FOREIGN KEY (`organizer`) REFERENCES `gk-users` (`id`);

ALTER TABLE `gk-races-krety`
CHANGE `raceGkId` `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
CHANGE `raceid` `race` int NOT NULL AFTER `id`,
CHANGE `geokretid` `geokret` int NOT NULL AFTER `race`,
CHANGE `initDist` `initial_distance` int NOT NULL AFTER `geokret`,
CHANGE `initCaches` `initial_caches_count` int NOT NULL AFTER `initial_distance`,
CHANGE `distToDest` `distance_to_destination` double NULL AFTER `initial_caches_count`,
CHANGE `joined` `joined_on_datetime` datetime NULL AFTER `distance_to_destination`,
CHANGE `finished` `finished_on_datetime` datetime NULL AFTER `joined_on_datetime`,
CHANGE `finishDist` `finish_distance` int NOT NULL AFTER `finished_on_datetime`,
CHANGE `finishCaches` `finish_caches_count` int NOT NULL AFTER `finish_distance`,
CHANGE `finishLat` `finish_lat` double NULL AFTER `finish_caches_count`,
CHANGE `finishLon` `finish_lon` double NULL AFTER `finish_lat`;
ALTER TABLE `gk-races-krety`
CHANGE `geokret` `geokret` int(11) unsigned NOT NULL AFTER `race`,
ADD FOREIGN KEY (`race`) REFERENCES `gk-races` (`id`),
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`);

ALTER TABLE `gk-ruchy-comments`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `move` `move` int(11) unsigned NOT NULL AFTER `id`,
CHANGE `geokret` `geokret` int(11) unsigned NOT NULL AFTER `move`,
CHANGE `author` `author` int(11) unsigned NOT NULL AFTER `geokret`,
RENAME TO `gk-move-comments`;

ALTER TABLE `gk-move-comments`
ADD FOREIGN KEY (`move`) REFERENCES `gk-moves` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE;

ALTER TABLE `gk-owner-codes`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `geokret` `geokret` int(11) unsigned NOT NULL AFTER `id`,
CHANGE `user` `user` int(11) unsigned NULL AFTER `claimed_on_datetime`;
ALTER TABLE `gk-owner-codes`
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE SET NULL;

ALTER TABLE `gk-waypointy`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST;


```


**Enable automatic datetime updates**

```sql

ALTER TABLE `gk-activation-codes`
CHANGE `created_on_datetime` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `token`,
ADD `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_on_datetime`;

ALTER TABLE `gk-badges`
CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
CHANGE `holder` `holder` int(11) unsigned NOT NULL AFTER `id`,
CHANGE `description` `description` varchar(128) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `holder`,
CHANGE `filename` `filename` varchar(32) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `description`,
CHANGE `awarded_on_datetime` `awarded_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `filename`,
ADD `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `gk-email-activation`
CHANGE `id` `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `user` `user` int(11) unsigned NOT NULL AFTER `token`,
CHANGE `created_on_datetime` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `confirmed`,
ADD `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE;

ALTER TABLE `gk-geokrety`
CHANGE `distance` `distance` int(10) unsigned NOT NULL DEFAULT '0' AFTER `owner`,
CHANGE `caches_count` `caches_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `distance`,
CHANGE `pictures_count` `pictures_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `caches_count`,
CHANGE `last_position` `last_position` int(11) unsigned NULL AFTER `pictures_count`,
CHANGE `last_log` `last_log` int(11) unsigned NULL AFTER `last_position`,
CHANGE `holder` `holder` int(11) unsigned NULL COMMENT 'In the hands of user' AFTER `last_log`,
CHANGE `missing` `missing` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `holder`,
CHANGE `type` `type` enum('0','1','2','3','4') COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `missing`,
CHANGE `avatar` `avatar` int(10) unsigned NULL AFTER `type`,
CHANGE `timestamp_oc` `timestamp_oc` datetime NOT NULL COMMENT 'Unused?' AFTER `avatar`,
CHANGE `created_on_datetime` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `timestamp_oc`,
CHANGE `updated_on_datetime` `updated_on_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_on_datetime`;

ALTER TABLE `gk-geokrety-rating`
ADD `rated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `rated_on_datetime`;

ALTER TABLE `gk-mail`
CHANGE `sent_on_datetime` `sent_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `content`;

ALTER TABLE `gk-move-comments`
CHANGE `content` `content` varchar(500) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `author`,
CHANGE `type` `type` tinyint(3) unsigned NOT NULL COMMENT '0=comment, 1=missing' AFTER `content`,
CHANGE `created_on_datetime` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `type`,
CHANGE `updated_on_datetime` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_on_datetime`;

ALTER TABLE `gk-moves`
CHANGE `author` `author` int(11) unsigned NULL DEFAULT '0' AFTER `waypoint`,
CHANGE `comment` `comment` varchar(5120) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `author`,
CHANGE `pictures_count` `pictures_count` tinyint(3) unsigned NULL DEFAULT '0' AFTER `comment`,
CHANGE `comments_count` `comments_count` smallint(5) unsigned NULL DEFAULT '0' AFTER `pictures_count`,
CHANGE `logtype` `logtype` enum('0','1','2','3','4','5','6') COLLATE 'utf8mb4_unicode_ci' NOT NULL COMMENT '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip' AFTER `comments_count`,
CHANGE `username` `username` varchar(20) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `logtype`,
CHANGE `app` `app` varchar(16) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'source of the log' AFTER `username`,
CHANGE `app_ver` `app_ver` varchar(16) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'application version/codename' AFTER `app`,
CHANGE `created_on_datetime` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `app_ver`,
CHANGE `moved_on_datetime` `moved_on_datetime` datetime NOT NULL COMMENT 'The move as configured by user' AFTER `created_on_datetime`,
CHANGE `updated_on_datetime` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `moved_on_datetime`;

ALTER TABLE `gk-news`
CHANGE `title` `title` varchar(50) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`,
CHANGE `content` `content` longtext COLLATE 'utf8mb4_unicode_ci' NULL AFTER `title`,
CHANGE `author_name` `author_name` varchar(80) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `content`,
CHANGE `author` `author` int(11) unsigned NULL AFTER `author_name`,
CHANGE `comments_count` `comments_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `author`,
CHANGE `created_on_datetime` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `comments_count`,
CHANGE `last_commented_on_datetime` `last_commented_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_on_datetime`;

ALTER TABLE `gk-news-comments`
CHANGE `content` `content` varchar(1000) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `author`,
CHANGE `icon` `icon` tinyint(3) unsigned NOT NULL AFTER `content`,
ADD `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `icon`,
CHANGE `updated_on_datetime` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_on_datetime`;

ALTER TABLE `gk-news-comments-access`
CHANGE `last_read_datetime` `last_read_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `user`;

ALTER TABLE `gk-owner-codes`
CHANGE `generated_on_datetime` `generated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `token`;

ALTER TABLE `gk-pictures`
CHANGE `created_on_datetime` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `caption`,
CHANGE `updated_on_datetime` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_on_datetime`;

ALTER TABLE `gk-races`
CHANGE `created_on_datetime` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation date' AFTER `id`,
CHANGE `updated_on_datetime` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_on_datetime`;

ALTER TABLE `gk-races-krety`
CHANGE `joined_on_datetime` `joined_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `distance_to_destination`,
ADD `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `joined_on_datetime`;

ALTER TABLE `gk-users`
CHANGE `joined_on_datetime` `joined_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `email_invalid`,
CHANGE `updated_on_datetime` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `joined_on_datetime`,
CHANGE `daily_mails` `daily_mails` tinyint(1) NOT NULL DEFAULT '1' AFTER `updated_on_datetime`,
CHANGE `registration_ip` `registration_ip` varchar(46) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `daily_mails`;

ALTER TABLE `gk-watched`
ADD `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `gk-waypointy`
ADD `added_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`,
CHANGE `updated_on_datetime` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `added_on_datetime`;
```

```sql
CREATE TABLE `gk-password-tokens` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `token` varchar(60) NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=unused 1=used',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_on_datetime` datetime NULL,
  `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `requesting_ip` varchar(46) NOT NULL,
  FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE,
  INDEX `token_used` (`token`, `used`),
  INDEX `created_on_datetime` (`created_on_datetime`)
) COMMENT='Retrieve user password' ENGINE='InnoDB' COLLATE 'utf8mb4_unicode_ci';

ALTER TABLE `gk-email-activation`
ADD INDEX `token` (`token`),
ADD `used_on_datetime` datetime NULL AFTER `created_on_datetime`,
ADD `previous_email` varchar(150) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'Store the previous in case of needed rollback' AFTER `user`,
ADD `updating_ip` varchar(46) COLLATE 'utf8mb4_unicode_ci' NULL,
CHANGE `confirmed` `used` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0=unused 1=validated 2=refused 3=expired' AFTER `email`;

ALTER TABLE `gk-email-activation`
CHANGE `updated_on_datetime` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_on_datetime`,
CHANGE `used_on_datetime` `used_on_datetime` datetime NULL AFTER `updated_on_datetime`,
ADD `revert_token` varchar(60) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `token`,
ADD `reverted_on_datetime` datetime NULL AFTER `used_on_datetime`,
ADD `reverting_ip` varchar(46) COLLATE 'utf8mb4_unicode_ci' NULL;

ALTER TABLE `gk-email-activation`
ADD INDEX `used_created_on_datetime_token` (`used`, `created_on_datetime`, `token`),
ADD INDEX `used_used_on_datetime_revert_token` (`used`, `used_on_datetime`, `revert_token`),
ADD INDEX `used_created_on_datetime_used_on_datetime` (`used`, `created_on_datetime`, `used_on_datetime`);
```

```sql
ALTER TABLE `gk-users`
ADD INDEX `username` (`username`),
ADD INDEX `username_email` (`username`, `email`),
ADD `terms_of_use_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Acceptation date' AFTER `last_login_datetime`,
ADD `account_valid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=unconfirmed 1=confirmed' AFTER `email`,
CHANGE `secid` `secid` varchar(128) COLLATE 'utf8mb4_unicode_ci' NOT NULL COMMENT 'connect by other applications' AFTER `terms_of_use_datetime`;

ALTER TABLE `gk-users`
CHANGE `terms_of_use_datetime` `terms_of_use_datetime` datetime NOT NULL COMMENT 'Acceptation date' AFTER `last_login_datetime`;

UPDATE `gk-users`
SET account_valid = 1
WHERE account_valid = 0;

CREATE TABLE `gk-account-activation` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `token` varchar(60) NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=unused 1=validated 2=expired',
  `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_on_datetime` datetime NULL,
  `requesting_ip` varchar(46) NOT NULL,
  `validating_ip` varchar(46) NULL,
  FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE
) ENGINE='InnoDB' COLLATE 'utf8mb4_unicode_ci';

ALTER TABLE `gk-account-activation`
ADD INDEX `used_created_on_datetime` (`used`, `created_on_datetime`);
```

```sql
ALTER TABLE `gk-moves`
CHANGE `app_ver` `app_ver` varchar(128) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'application version/codename' AFTER `app`;
```
