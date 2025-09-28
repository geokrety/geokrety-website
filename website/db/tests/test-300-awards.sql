-- Start transaction and plan the tests.
BEGIN;

SELECT plan(10);

-- Setup test data starting at ID 1 (following convention)
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');

-- Test awards
INSERT INTO "gk_awards" ("id", "name", "description", "filename", "type") VALUES (1, 'Test Award', 'Test award description', 'test.svg', 'manual');
INSERT INTO "gk_awards" ("id", "name", "description", "filename", "type") VALUES (2, 'Hidden GeoKrety Finder', 'Has discovered one Hidden GeoKrety', 'hidden-finder.svg', 'manual');
INSERT INTO "gk_awards" ("id", "name", "description", "filename", "type") VALUES (3, 'Another Award', 'Another test award', 'another.svg', 'manual');

-- Test basic award assignment works
SELECT lives_ok($$INSERT INTO "gk_awards_won" ("holder", "award", "description") VALUES (1, 1, 'Test award assignment')$$, 'Basic award assignment should work');

-- Test unique constraint (holder, award) - user cannot get same award twice
SELECT throws_ok(
    $$INSERT INTO "gk_awards_won" ("holder", "award", "description") VALUES (1, 1, 'Duplicate award')$$,
    23505,
    'duplicate key value violates unique constraint "gk_awards_won_holder_award"',
    'Should prevent duplicate awards to same user'
);

-- Test same award can be given to different users
SELECT lives_ok($$INSERT INTO "gk_awards_won" ("holder", "award", "description") VALUES (2, 1, 'Same award to different user')$$, 'Same award can be given to different users');

-- Test different awards can be given to same user
SELECT lives_ok($$INSERT INTO "gk_awards_won" ("holder", "award", "description") VALUES (1, 3, 'Different award to same user')$$, 'Different awards can be given to same user');

-- Test Easter Egg GeoKrety setup (type 10)
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (1, 'Easter Egg Test', 10, 1, 1, '2024-07-21 12:15:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (2, 'Regular GeoKret', 0, 1, 1, '2024-07-21 12:15:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (3, 'Book GeoKret', 1, 2, 2, '2024-07-21 12:15:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (4, 'Another Easter Egg', 10, 2, 2, '2024-07-21 12:15:00+00');

-- Test award assignment can happen to different users for Hidden GeoKrety Finder
SELECT lives_ok($$INSERT INTO "gk_awards_won" ("holder", "award", "description") VALUES (1, 2, 'Found Easter Egg')$$, 'User 1 can get Hidden GeoKrety Finder award');
SELECT lives_ok($$INSERT INTO "gk_awards_won" ("holder", "award", "description") VALUES (2, 2, 'Found Easter Egg')$$, 'User 2 can get Hidden GeoKrety Finder award');
SELECT lives_ok($$INSERT INTO "gk_awards_won" ("holder", "award", "description") VALUES (3, 2, 'Found Easter Egg')$$, 'User 3 can get Hidden GeoKrety Finder award');

-- Test same user cannot get Hidden GeoKrety Finder award twice
SELECT throws_ok(
    $$INSERT INTO "gk_awards_won" ("holder", "award", "description") VALUES (1, 2, 'Duplicate Easter Egg award')$$,
    23505,
    'duplicate key value violates unique constraint "gk_awards_won_holder_award"',
    'Should prevent duplicate Hidden GeoKrety Finder awards to same user'
);

-- Test award lookup by name functionality
SELECT ok(EXISTS(SELECT 1 FROM gk_awards WHERE name = 'Hidden GeoKrety Finder'), 'Hidden GeoKrety Finder award exists in database');
SELECT ok(EXISTS(SELECT 1 FROM gk_awards WHERE name = 'GK Developer'), 'GK Developer exists in database');

SELECT * FROM finish();
ROLLBACK;
