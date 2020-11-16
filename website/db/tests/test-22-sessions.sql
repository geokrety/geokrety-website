-- Start transaction and plan the tests.

BEGIN;
-- SELECT plan(49);
SELECT * FROM no_plan();

-- Play deferred triggers immediately (moves stats update)
SET CONSTRAINTS ALL IMMEDIATE;

-- on_behalf automatically generated
INSERT INTO "sessions" ("session_id") VALUES ('test 1');
SELECT isnt(on_behalf, null ) FROM sessions WHERE session_id='test 1';

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
