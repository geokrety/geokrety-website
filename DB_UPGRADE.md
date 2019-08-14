
```
## Migrate schema

```sql
ALTER TABLE `gk-news-comments`
CHANGE `date` `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `user_id`;
```

## Create views
```sql
CREATE VIEW `users` AS
SELECT `userid` as id,`user` as username,`haslo2` as password, email, email_invalid, joined as joined_on_datetime, wysylacmaile as daily_mails, ip, timestamp as updated_on_datetime, lang as prefered_language, lat as home_latitude, lon as home_longitude, country as home_country, statpic as statpic_template_id, ostatni_mail as last_mail_datetime, ostatni_login as last_login_datetime, secid
FROM `gk-users`;
```

```sql
CREATE VIEW `site_values` AS
SELECT `name`, value
FROM `gk-wartosci`;
```

```sql
CREATE VIEW `news` AS
SELECT news_id as id, date as updated_on_datetime, tytul as title, tresc as content, userid as author, who as author_name, komentarze as comments_count, ostatni_komentarz as last_comment_datetime
FROM `gk-news`;
```

```sql
CREATE VIEW `news_comments` AS
SELECT comment_id as id, news_id as news, user_id as author, comment as content, icon, date as updated_on_datetime
FROM `gk-news-comments`;
```

```sql
CREATE VIEW `news_subscriptions` AS
SELECT news_id as news, user_id as user, `read` as last_read_datetime, subscribed
FROM `gk-news-comments-access`;
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

```

```sql
ALTER TABLE `gk-ruchy`
CHANGE `ruch_id` `id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `id` `geokret` int(10) unsigned NOT NULL AFTER `id`,
CHANGE `country` `country` varchar(3) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'ISO 3166-1 https://fr.wikipedia.org/wiki/ISO_3166-1' AFTER `alt`,
CHANGE `droga` `distance` int(10) unsigned NULL AFTER `country`,
CHANGE `data` `created_on_datetime` datetime NULL ON UPDATE CURRENT_TIMESTAMP AFTER `waypoint`,
CHANGE `data_dodania` `moved_on_datetime` datetime NULL COMMENT 'The move as configured by user' AFTER `created_on_datetime`,
CHANGE `koment` `comment` varchar(5120) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `user`,
CHANGE `zdjecia` `pictures_count` tinyint(3) unsigned NULL DEFAULT '0' AFTER `comment`,
CHANGE `komentarze` `comments_count` smallint(5) unsigned NULL DEFAULT '0' AFTER `pictures_count`,
CHANGE `user` `author` int(10) unsigned NULL DEFAULT '0' AFTER `moved_on_datetime`,
CHANGE `timestamp` `updated_on_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `username`;
```

```sql
ALTER TABLE `gk-users`
CHANGE `userid` `id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST,
CHANGE `user` `username` varchar(80) COLLATE 'utf8mb4_polish_ci' NULL AFTER `id`,
CHANGE `haslo` `old_password` varchar(500) COLLATE 'utf8mb4_unicode_ci' NULL COMMENT 'This hash is not used anymore' AFTER `username`,
CHANGE `haslo2` `password` varchar(120) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `old_password`,
CHANGE `email` `email` varchar(150) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `password`,
CHANGE `joined` `joined_on_datetime` datetime NULL AFTER `email_invalid`,
CHANGE `wysylacmaile` `daily_mails` binary(1) NOT NULL DEFAULT '1' AFTER `joined_on_datetime`,
CHANGE `ip` `registration_ip` varchar(46) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `daily_mails`,
CHANGE `timestamp` `updated_on_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `registration_ip`,
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
CHANGE `timestamp` `updated_on_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `status`;
ALTER TABLE `gk-waypointy`
ADD `id` int unsigned NOT NULL AUTO_INCREMENT UNIQUE FIRST;
ALTER TABLE `gk-waypointy`
ADD PRIMARY KEY `id` (`id`),
ADD INDEX (`waypoint`),
DROP INDEX `PRIMARY`,
DROP INDEX `id`,
DROP INDEX `name`;
```

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
CHANGE `timestamp` `updated_on_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `type`;
```

```sql
ALTER TABLE `gk-badges`
CHANGE `userid` `holder` bigint(20) NOT NULL AFTER `id`,
CHANGE `timestamp` `awarded_on_datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `user`,
CHANGE `desc` `description` varchar(128) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `awarded_on_datetime`,
CHANGE `file` `filename` varchar(32) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `description`;
```
