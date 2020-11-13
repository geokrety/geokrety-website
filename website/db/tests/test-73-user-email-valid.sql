-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(6);

-- const USER_EMAIL_NO_ERROR = 0;
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "email_invalid") VALUES (1, 'username1', '127.0.0.1', 0::smallint)$$);
-- const USER_EMAIL_INVALID = 1;
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "email_invalid") VALUES (2, 'username2', '127.0.0.1', 1::smallint)$$);
-- const USER_EMAIL_UNCONFIRMED = 2;
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "email_invalid") VALUES (3, 'username3', '127.0.0.1', 2::smallint)$$);
-- const USER_EMAIL_MISSING = 3;
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "email_invalid") VALUES (4, 'username4', '127.0.0.1', 3::smallint)$$);

SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "email_invalid") VALUES (5, 'username5', '127.0.0.1', 4::smallint)$$);
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "email_invalid") VALUES (6, 'username6', '127.0.0.1', 5::smallint)$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
;
