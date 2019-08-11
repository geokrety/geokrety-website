
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
CHANGE `komentarze` `coumments_count` smallint(5) unsigned NULL DEFAULT '0' AFTER `pictures_count`,
CHANGE `user` `author` int(10) unsigned NULL DEFAULT '0' AFTER `moved_on_datetime`,
CHANGE `timestamp` `updated_on_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `username`;
```
