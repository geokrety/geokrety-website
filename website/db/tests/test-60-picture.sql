-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();
-- SELECT plan(3);
\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_secid") VALUES (1, 'test', '127.0.0.1', 'qwertyuiop');
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);


SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "geokret", "type") VALUES (2, 1, 1, 0)$$);
SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "type") VALUES (3, 1, 1, 1)$$);
SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "user", "type") VALUES (4, 1, 1, 2)$$);

SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "type") VALUES (5, 1, 0)$$, 22000, 'One of Geokret (<NULL>), Move (<NULL>) or User (<NULL>) must be specified');
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (6, 1, NULL, NULL, NULL, 0)$$);
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (7, 1, NULL, NULL, NULL, 1)$$);
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (8, 1, NULL, NULL, NULL, 2)$$);
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (9, 1, NULL, NULL, NULL, 3)$$);

SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (10, 1, NULL, NULL, 1, 0)$$);
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (11, 1, 1, NULL, NULL, 0)$$);
SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (12, 1, NULL, 1, NULL, 0)$$);

SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (13, 1, NULL, NULL, 1, 1)$$);
SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (14, 1, 1, NULL, NULL, 1)$$);
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (15, 1, NULL, 1, NULL, 1)$$);
SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (16, 1, 1, 1, NULL, 1)$$);

SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (17, 1, NULL, NULL, 1, 2)$$);
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (18, 1, 1, NULL, NULL, 2)$$);
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (19, 1, NULL, 1, NULL, 2)$$);

SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (19, 1, 1, 1, 1, 1)$$, 22000, 'Picture `type` does not match the specified arguments.');
SELECT throws_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "user", "type") VALUES (19, 1, 1, 1, 1, 6)$$, 22000, 'Picture type unrecognized (6)');

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
