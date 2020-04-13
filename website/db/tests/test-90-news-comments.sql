-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(9);
\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- news comments counter incremented decremented on insert
INSERT INTO "gk_news" ("id", "title", "content") VALUES (1, 'test', 'content');
INSERT INTO "gk_news_comments" ("id", "news", "author", "content") VALUES (1, 1, 1, 'content');
SELECT is(comments_count, 1, 'comments_count incremented') from gk_news WHERE id = 1::bigint;

-- news comments counter untouched on simple edit
INSERT INTO "gk_news" ("id", "title", "content") VALUES (2, 'test', 'content');
INSERT INTO "gk_news_comments" ("id", "news", "author", "content") VALUES (2, 2, 1, 'content');
UPDATE "gk_news_comments" SET content='new content' WHERE id = 2::bigint;
SELECT is(comments_count, 1, 'comments_count untouched') from gk_news WHERE id = 2::bigint;

-- news comments counter incremented decremented on update move
INSERT INTO "gk_news" ("id", "title", "content") VALUES (3, 'test', 'content');
INSERT INTO "gk_news" ("id", "title", "content") VALUES (4, 'test', 'content');
INSERT INTO "gk_news_comments" ("id", "news", "author", "content") VALUES (3, 3, 1, 'content');
UPDATE "gk_news_comments" SET news=4 WHERE id = 3::bigint;
SELECT is(comments_count, 0, 'comments_count decremented') from gk_news WHERE id = 3::bigint;
SELECT is(comments_count, 1, 'comments_count incremented on the other news') from gk_news WHERE id = 4::bigint;

-- news comments counter decremented on delete
INSERT INTO "gk_news" ("id", "title", "content") VALUES (5, 'test', 'content');
INSERT INTO "gk_news_comments" ("id", "news", "author", "content") VALUES (4, 5, 1, 'content');
INSERT INTO "gk_news_comments" ("id", "news", "author", "content") VALUES (7, 5, 1, 'content');
SELECT is(comments_count, 2, 'we have 2 comments') from gk_news WHERE id = 5::bigint;
DELETE FROM "gk_news_comments" WHERE id = 4::bigint;
SELECT is(comments_count, 1, 'comments_count decremented on delete') from gk_news WHERE id = 5::bigint;

-- delete news, deletes comments too
INSERT INTO "gk_news" ("id", "title", "content") VALUES (6, 'test', 'content');
INSERT INTO "gk_news_comments" ("id", "news", "author", "content") VALUES (5, 6, 1, 'content');
DELETE FROM "gk_news" WHERE id = 6::bigint;
SELECT is(count(*), 0::bigint, 'delete news deletes news comments') from gk_news_comments WHERE news = 6::bigint;

-- update news counter manually is forbidden
INSERT INTO "gk_news" ("id", "title", "content") VALUES (7, 'test', 'content');
INSERT INTO "gk_news_comments" ("id", "news", "author", "content") VALUES (6, 7, 1, 'content');
SELECT lives_ok($$UPDATE "gk_news" SET comments_count=42 WHERE id = 7::bigint$$);
SELECT is(comments_count, 1, 'news comments cannot be overridden') from gk_news WHERE id = 7::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
