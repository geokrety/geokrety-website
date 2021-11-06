-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(11);

-- cannot ack non locked script
INSERT INTO "scripts" ("id", "name") VALUES (1, 'test 1');
SELECT throws_ok($$UPDATE "scripts" SET "acked_on_datetime" = '2021-11-06 18:15:00+00' WHERE id = 1::bigint;$$);
SELECT is("acked_on_datetime", NULL) FROM scripts WHERE id = 1::bigint;
SELECT is("locked_on_datetime", NULL) FROM scripts WHERE id = 1::bigint;

INSERT INTO "scripts" ("id", "name", "locked_on_datetime") VALUES (2, 'test 2', '2021-11-06 18:15:01+00');
SELECT lives_ok($$UPDATE "scripts" SET "acked_on_datetime" = '2021-11-06 18:15:00+00' WHERE id = 2::bigint;$$);
SELECT is("locked_on_datetime", '2021-11-06 18:15:01+00') FROM scripts WHERE id = 2::bigint;
SELECT is("acked_on_datetime", '2021-11-06 18:15:00+00') FROM scripts WHERE id = 2::bigint;

-- unlock automatically also unack
INSERT INTO "scripts" ("id", "name", "locked_on_datetime", "acked_on_datetime") VALUES (3, 'test 3', '2021-11-06 18:40:00+00', '2021-11-06 18:41:00+00');
SELECT is("locked_on_datetime", '2021-11-06 18:40:00+00') FROM scripts WHERE id = 3::bigint;
SELECT is("acked_on_datetime", '2021-11-06 18:41:00+00') FROM scripts WHERE id = 3::bigint;
SELECT lives_ok($$UPDATE "scripts" SET "locked_on_datetime" = NULL WHERE id = 3::bigint;$$);
SELECT is("locked_on_datetime", NULL) FROM scripts WHERE id = 3::bigint;
SELECT is("acked_on_datetime", NULL) FROM scripts WHERE id = 3::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
