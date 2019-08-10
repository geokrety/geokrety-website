
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
