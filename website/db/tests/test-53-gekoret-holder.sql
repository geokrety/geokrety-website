-- Start transaction and plan the tests.

BEGIN;
-- SELECT * FROM no_plan();
SELECT plan(21);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');

-- On create holder is owner
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
SELECT is(holder, owner, 'On create holder is owner') FROM gk_geokrety WHERE id = 1::bigint;
SELECT is(holder, NULL, 'On create holder is owner') FROM gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "owner") VALUES (2, 'test', 0, 1);
SELECT is(holder, owner, 'On create holder is owner') FROM gk_geokrety WHERE id = 2::bigint;
SELECT is(holder, 1::bigint, 'On create holder is owner') FROM gk_geokrety WHERE id = 2::bigint;

-- On create holder can not be overridden
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (3, 'test', 0, 1, NULL);
SELECT is(holder, owner, 'holder can be overridden') FROM gk_geokrety WHERE id = 3::bigint;
SELECT is(holder, 1::bigint, 'holder can not be overridden') FROM gk_geokrety WHERE id = 3::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (4, 'test', 0, 1, 2);
SELECT is(holder, owner, 'holder can not be overridden') FROM gk_geokrety WHERE id = 4::bigint;
SELECT is(holder, 1::bigint, 'holder can not be overridden') FROM gk_geokrety WHERE id = 4::bigint;

-- recompute holder via set NULL, same is OK, other are refused
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (5, 'test', 0, 1, 2);
UPDATE "gk_geokrety" SET holder = NULL WHERE id = 5::bigint;
SELECT is(holder, 1::bigint, 'reset holder') FROM gk_geokrety WHERE id = 5::bigint;
SELECT lives_ok($$UPDATE "gk_geokrety" SET holder = 2 WHERE id = 5::bigint;$$);
SELECT is(holder, 1::bigint, 'reset holder') FROM gk_geokrety WHERE id = 5::bigint;
SELECT lives_ok($$UPDATE "gk_geokrety" SET holder = 1 WHERE id = 5::bigint;$$);
SELECT is(holder, 1::bigint, 'reset holder') FROM gk_geokrety WHERE id = 5::bigint;

-- holder from last position - drop
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "created_on_datetime") VALUES (6, 'test 1', 0, 3, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 6, 2, :nice, '2020-04-07 00:00:00+00', 0);
SELECT is(holder, NULL, 'holder from last position - drop') FROM gk_geokrety WHERE id = 6::bigint;

-- holder from last position - grab
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "created_on_datetime") VALUES (7, 'test 1', 0, 3, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (2, 7, 1, '2020-04-07 00:00:00+00', 0, :nice);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (3, 7, 2, '2020-04-07 01:00:00+00', 1);
SELECT is(holder, 2::bigint, 'holder from last position - grab') FROM gk_geokrety WHERE id = 7::bigint;

-- 16: holder from last position - seen (last was in cache)
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "created_on_datetime") VALUES (8, 'test 1', 0, 3, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (4, 8, 1, '2020-04-07 00:00:00+00', 0, :nice);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (5, 8, 2, '2020-04-07 01:00:00+00', 3, :paris);
SELECT is(holder, NULL, 'holder from last position - seen (from cache)') FROM gk_geokrety WHERE id = 8::bigint;

-- holder from last position - seen (last was NOT in cache)
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "created_on_datetime") VALUES (9, 'test 1', 0, 3, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (6, 9, 1, '2020-04-07 00:00:00+00', 1);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 9, 2, '2020-04-07 01:00:00+00', 3, :paris);
SELECT is(holder, 1::bigint, 'holder from last position - seen (from hands)') FROM gk_geokrety WHERE id = 9::bigint;

-- holder from last position - seen (recurse multiple)
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "created_on_datetime") VALUES (10, 'test 1', 0, 3, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (8, 10, 1, '2020-04-07 00:00:00+00', 1);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 10, 2, '2020-04-07 01:00:00+00', 3, :paris);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (10, 10, 2, '2020-04-07 02:00:00+00', 3, :paris);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (11, 10, 2, '2020-04-07 03:00:00+00', 3, :paris);
SELECT is(holder, 1::bigint, 'holder from last position - seen (from hands)') FROM gk_geokrety WHERE id = 10::bigint;

-- holder from last position - comment
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "created_on_datetime") VALUES (11, 'test 1', 0, 3, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (12, 11, 1, '2020-04-07 00:00:00+00', 1);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (13, 11, 2, '2020-04-07 01:00:00+00', 2);
SELECT is(holder, 1::bigint, 'holder from last position - comment') FROM gk_geokrety WHERE id = 11::bigint;

-- holder from last position - archive
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "created_on_datetime") VALUES (12, 'test 1', 0, 3, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (14, 12, 1, '2020-04-07 00:00:00+00', 1);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (15, 12, 3, '2020-04-07 01:00:00+00', 4);
SELECT is(holder, NULL, 'holder from last position - archive') FROM gk_geokrety WHERE id = 12::bigint;

-- holder from last position - dip
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "created_on_datetime") VALUES (13, 'test 1', 0, 3, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (16, 13, 1, '2020-04-07 00:00:00+00', 1);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (17, 13, 2, '2020-04-07 01:00:00+00', 5, :nice);
SELECT is(holder, 2::bigint, 'holder from last position - dip') FROM gk_geokrety WHERE id = 13::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
