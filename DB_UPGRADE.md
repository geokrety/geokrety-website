
```
## Migrate schema

```sql
ALTER TABLE `gk-news-comments`
CHANGE `date` `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `user_id`;
```

## Create views
```sql
CREATE VIEW `site_values` AS
SELECT `name`, value
FROM `gk-wartosci`;
```

```sql
ALTER TABLE `gk-news`
CHANGE `news_id` `id` bigint(20) NOT NULL AUTO_INCREMENT FIRST,
CHANGE `date` `created_on_datetime` datetime NULL DEFAULT CURRENT_TIMESTAMP AFTER `id`,
DROP `czas_postu`,
CHANGE `tytul` `title` varchar(50) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `created_on_datetime`,
CHANGE `tresc` `content` longtext COLLATE 'utf8mb4_unicode_ci' NULL AFTER `title`,
CHANGE `who` `author_name` varchar(80) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `content`,
CHANGE `userid` `author` int(10) unsigned NULL AFTER `author_name`,
CHANGE `komentarze` `comments_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `author`,
CHANGE `ostatni_komentarz` `last_commented_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `comments_count`,
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`);
```


```sql
ALTER TABLE `gk-news-comments-access`
CHANGE `news_id` `news` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `user_id` `user` int(10) unsigned NOT NULL AFTER `news`,
CHANGE `read` `last_read_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `user`,
CHANGE `post` `last_post_datetime` datetime NULL AFTER `last_read_datetime`;
```

```sql
ALTER TABLE `gk-geokrety`
CHANGE `nr` `tracking_code` varchar(9) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`,
CHANGE `nazwa` `name` varchar(75) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `tracking_code`,
CHANGE `opis` `mission` mediumtext COLLATE 'utf8mb4_unicode_ci' NULL AFTER `name`,
CHANGE `data` `created_on_datetime` datetime NULL DEFAULT CURRENT_TIMESTAMP AFTER `owner`,
CHANGE `droga` `distance` int(10) unsigned NOT NULL AFTER `created_on_datetime`,
CHANGE `skrzynki` `caches_count` smallint(5) unsigned NOT NULL AFTER `distance`,
CHANGE `zdjecia` `pictures_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `caches_count`,
CHANGE `ost_pozycja_id` `last_position` int(10) unsigned NOT NULL AFTER `pictures_count`,
CHANGE `ost_log_id` `last_log` int(10) unsigned NOT NULL AFTER `last_position`,
CHANGE `hands_of` `holder` int(10) unsigned NULL COMMENT 'In the hands of user' AFTER `loast_log`,
CHANGE `typ` `type` enum('0','1','2','3','4') COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `missing`,
CHANGE `avatarid` `avatar` int(10) unsigned NOT NULL AFTER `type`,
CHANGE `timestamp_oc` `timestamp_oc` datetime NOT NULL COMMENT 'Unused?' AFTER `avatar`,
CHANGE `timestamp` `updated_on_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `timestamp_oc`;

ALTER TABLE `gk-geokrety`
CHANGE `distance` `distance` int(10) unsigned NOT NULL DEFAULT '0' AFTER `created_on_datetime`,
CHANGE `caches_count` `caches_count` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `distance`,
CHANGE `last_position` `last_position` int(10) unsigned NULL AFTER `pictures_count`,
CHANGE `last_log` `last_log` int(10) unsigned NULL AFTER `last_position`,
CHANGE `avatar` `avatar` int(10) unsigned NULL AFTER `type`;

UPDATE `gk-geokrety` SET `last_log` = NULL WHERE `last_log` = 0;
UPDATE `gk-geokrety` SET `last_position` = NULL WHERE `last_position` = 0;
UPDATE `gk-geokrety` SET `holder` = NULL WHERE `holder` = 0;
```

**Workaround security issue**
```sql
ALTER TABLE `gk-geokrety`
ADD `gkid` int(10) unsigned NOT NULL COMMENT 'The real GK id : https://stackoverflow.com/a/33791018/944936' AFTER `id`;

update `gk-geokrety`
set gkid=id

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
CHANGE `data` `created_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `waypoint`,
CHANGE `data_dodania` `moved_on_datetime` datetime NOT NULL COMMENT 'The move as configured by user' AFTER `created_on_datetime`,
CHANGE `koment` `comment` varchar(5120) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `user`,
CHANGE `zdjecia` `pictures_count` tinyint(3) unsigned NULL DEFAULT '0' AFTER `comment`,
CHANGE `komentarze` `comments_count` smallint(5) unsigned NULL DEFAULT '0' AFTER `pictures_count`,
CHANGE `user` `author` int(10) unsigned NULL DEFAULT '0' AFTER `moved_on_datetime`,
CHANGE `timestamp` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `username`,
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
SELECT *
FROM `gk-ruchy`
WHERE author NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

UPDATE `gk-ruchy`
SET author = NULL
WHERE author IN (18747, 30494, 37469);

UPDATE `gk-ruchy` SET `author` = NULL WHERE `author` = 0;
ALTER TABLE `gk-ruchy`
CHANGE `author` `author` int(10) unsigned NULL AFTER `moved_on_datetime`,
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`);

SELECT distinct(geokret)
FROM `gk-ruchy`
WHERE geokret NOT IN (SELECT DISTINCT id FROM `gk-geokrety`)
LIMIT 50;

UPDATE `gk-ruchy`
SET geokret = NULL
WHERE geokret IN (0, 5295, 15335, 30163, 30164, 30166, 30168, 30169, 30170, 65632, 65633, 65634, 65635, 65636, 65637, 65638, 65642);

DELETE FROM `gk-ruchy`
WHERE geokret = 0;

ALTER TABLE `gk-ruchy`
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`),
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`);
```

```sql
ALTER TABLE `gk-users`
CHANGE `userid` `id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `user` `username` varchar(80) COLLATE 'utf8mb4_polish_ci' NOT NULL AFTER `id`,
CHANGE `haslo` `old_password` varchar(500) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'This hash is not used anymore' AFTER `username`,
CHANGE `haslo2` `password` varchar(120) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `old_password`,
CHANGE `email` `email` varchar(150) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `password`,
CHANGE `joined` `joined_on_datetime` datetime NULL AFTER `email_invalid`,
CHANGE `wysylacmaile` `daily_mails` tinyint(1) NOT NULL DEFAULT '1' AFTER `joined_on_datetime`,
CHANGE `ip` `registration_ip` varchar(46) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `daily_mails`,
CHANGE `timestamp` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `registration_ip`,
CHANGE `lang` `prefered_language` varchar(2) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `updated_on_datetime`,
CHANGE `lat` `home_latitude` double(8,5) NULL AFTER `prefered_language`,
CHANGE `lon` `home_longitude` double(8,5) NULL AFTER `home_latitude`,
CHANGE `promien` `observation_area` smallint(5) unsigned NULL AFTER `home_longitude`,
CHANGE `country` `home_country` char(3) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `observation_area`,
CHANGE `godzina` `daily_mails_hour` int(11) NOT NULL AFTER `home_country`,
CHANGE `statpic` `statpic_template_id` tinyint(1) NOT NULL DEFAULT '1' AFTER `daily_mails_hour`,
CHANGE `ostatni_mail` `last_mail_datetime` datetime NULL AFTER `statpic_template_id`,
CHANGE `ostatni_login` `last_login_datetime` datetime NULL AFTER `last_mail_datetime`;
```

```sql
ALTER TABLE `gk-waypointy`
CHANGE `typ` `type` varchar(200) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `owner`,
CHANGE `kraj` `country_name` varchar(200) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'full English country name' AFTER `type`,
CHANGE `timestamp` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `status`;
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
CHANGE `data_dodania` `created_on_datetime` datetime NULL DEFAULT CURRENT_TIMESTAMP AFTER `author`,
CHANGE `comment` `content` varchar(500) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `created_on_datetime`,
CHANGE `type` `type` tinyint(3) unsigned NOT NULL COMMENT '0=comment, 1=missing' AFTER `content`,
CHANGE `timestamp` `updated_on_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `type`,
ADD FOREIGN KEY (`move`) REFERENCES `gk-ruchy` (`id`),
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`),
ADD FOREIGN KEY (`author`) REFERENCES `gk-users` (`id`);
```


**Fix gk-badges holders**
```sql
SELECT *
FROM `gk-badges`
WHERE holder NOT IN (SELECT DISTINCT id FROM `gk-users`)
LIMIT 50;

DELETE FROM `gk-badges`
WHERE holder = 16957;
```

```sql
ALTER TABLE `gk-badges`
CHANGE `userid` `holder` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `timestamp` `awarded_on_datetime` datetime NULL DEFAULT CURRENT_TIMESTAMP AFTER `user`,
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
CHANGE `timestamp` `created_on_datetime` datetime NULL DEFAULT CURRENT_TIMESTAMP AFTER `confirmed`,
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`) ON DELETE CASCADE,
RENAME TO `gk-email-activation`;
```

```sql
ALTER TABLE `gk-maile`
CHANGE `id_maila` `id` bigint(20) NOT NULL AUTO_INCREMENT FIRST,
CHANGE `random_string` `token` varchar(10) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`,
CHANGE `temat` `subject` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `to`,
CHANGE `tresc` `content` mediumtext COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `subject`,
CHANGE `timestamp` `sent_on_datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `content`,
CHANGE `ip` `ip` varchar(46) NOT NULL AFTER `sent_on_datetime`,
RENAME TO `gk-mail`;
```

```sql
ALTER TABLE `gk-owner-codes`
CHANGE `kret_id` `geokret` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `code` `token` varchar(20) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `geokret`,
CHANGE `generated_date` `generated_on_datetime` datetime NULL DEFAULT CURRENT_TIMESTAMP AFTER `token`,
CHANGE `claimed_date` `claimed_on_datetime` datetime NULL AFTER `generated_on_datetime`,
CHANGE `user_id` `user` int(10) unsigned NULL AFTER `claimed_on_datetime`,
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`),
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`);

UPDATE `gk-owner-codes` SET `claimed_on_datetime` = NULL, `user` = NULL WHERE `user` = '0';
```

```sql
ALTER TABLE `gk-obserwable`
CHANGE `userid` `user` int(10) unsigned NOT NULL FIRST,
CHANGE `id` `geokret` int(10) unsigned NOT NULL AFTER `user`,
ADD FOREIGN KEY (`user`) REFERENCES `gk-users` (`id`),
ADD FOREIGN KEY (`geokret`) REFERENCES `gk-geokrety` (`id`),
RENAME TO `gk-watched`;
```





