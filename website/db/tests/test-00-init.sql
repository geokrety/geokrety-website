-- Start transaction and plan the tests.
BEGIN;
SELECT plan(1);

-- Run the tests.
SELECT pass( 'pgTAP is active!' );

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
