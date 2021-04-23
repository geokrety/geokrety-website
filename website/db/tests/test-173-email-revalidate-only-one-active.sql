-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(32);

\set TOKEN_UNUSED   0::smallint
\set TOKEN_VALID    1::smallint
\set TOKEN_EXPIRED  2::smallint
\set TOKEN_DISABLED 3::smallint

\set USER_ACCOUNT_INVALID 0::smallint
\set USER_ACCOUNT_VALID 1::smallint
\set USER_ACCOUNT_IMPORTED 2::smallint

INSERT INTO "gk_users" ("id", "username", "registration_ip", "account_valid") VALUES (1, 'test 1', '127.0.0.1', :USER_ACCOUNT_IMPORTED);
INSERT INTO "gk_users" ("id", "username", "registration_ip", "account_valid") VALUES (2, 'test 2', '127.0.0.1', :USER_ACCOUNT_IMPORTED);

-- only one active token per geokret
INSERT INTO "gk_email_revalidate" ("id", "user", "_email") VALUES (1, 1, 'test+1@geokrety.org');
SELECT is("used", :TOKEN_UNUSED) FROM gk_email_revalidate WHERE id = 1::bigint;

INSERT INTO "gk_email_revalidate" ("id", "user", "_email") VALUES (2, 1, 'test+1@geokrety.org');
SELECT is("used", :TOKEN_DISABLED) FROM gk_email_revalidate WHERE id = 1::bigint;
SELECT is("used", :TOKEN_UNUSED) FROM gk_email_revalidate WHERE id = 2::bigint;

INSERT INTO "gk_email_revalidate" ("id", "user", "_email") VALUES (3, 1, 'test+1@geokrety.org');
SELECT is("used", :TOKEN_DISABLED) FROM gk_email_revalidate WHERE id = 1::bigint;
SELECT is("used", :TOKEN_DISABLED) FROM gk_email_revalidate WHERE id = 2::bigint;
SELECT is("used", :TOKEN_UNUSED) FROM gk_email_revalidate WHERE id = 3::bigint; -- 6

-- reset to unused
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "validated_on_datetime", "validating_ip", "used")
        VALUES (4, 1, 'test+8@geokrety.org', '2020-04-07 04:00:00+00', '127.0.0.1', :TOKEN_VALID);
UPDATE "gk_email_revalidate" SET "used" = :TOKEN_UNUSED, "validated_on_datetime"=NULL, "validating_ip"=NULL WHERE id = 4::bigint;
SELECT is("used", :TOKEN_DISABLED) FROM gk_email_revalidate WHERE id = 1::bigint;
SELECT is("used", :TOKEN_DISABLED) FROM gk_email_revalidate WHERE id = 2::bigint;
SELECT is("used", :TOKEN_DISABLED) FROM gk_email_revalidate WHERE id = 3::bigint;
SELECT is("used", :TOKEN_UNUSED)   FROM gk_email_revalidate WHERE id = 4::bigint; -- 10
SELECT lives_ok($$UPDATE "gk_email_revalidate" SET "used" = 0::smallint WHERE id = 4::bigint$$);

-- Other status don't interfere
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (5, 1, 'test+1@geokrety.org', :TOKEN_EXPIRED);
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (6, 1, 'test+1@geokrety.org', :TOKEN_EXPIRED);
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (7, 1, 'test+1@geokrety.org', :TOKEN_DISABLED);
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (8, 1, 'test+1@geokrety.org', :TOKEN_DISABLED);
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "validated_on_datetime", "validating_ip", "used") VALUES (9, 1, 'test+1@geokrety.org', '2020-04-07 04:00:00+00', '127.0.0.1', :TOKEN_VALID);
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "validated_on_datetime", "validating_ip", "used") VALUES (10, 1, 'test+1@geokrety.org', '2020-04-07 04:00:00+00', '127.0.0.1', :TOKEN_VALID);
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (11, 1, 'test+1@geokrety.org', :TOKEN_UNUSED);
SELECT is("used", :TOKEN_DISABLED) FROM gk_email_revalidate WHERE id = 1::bigint;

-- Normal flow
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (12, 1, 'test+1@geokrety.org', :TOKEN_UNUSED);
SELECT lives_ok($$UPDATE "gk_email_revalidate" SET "used" = 1::smallint, "validated_on_datetime" = '2020-04-07 04:00:00+00', "validating_ip" = '127.0.0.1' WHERE id = 12::bigint;$$);
SELECT is("account_valid", :USER_ACCOUNT_VALID) FROM gk_users WHERE id = 1::bigint;

-- datetime is automatic
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (13, 1, 'test+1@geokrety.org', :TOKEN_UNUSED);
SELECT lives_ok($$UPDATE "gk_email_revalidate" SET "used" = 1::smallint, "validating_ip" = '127.0.0.1' WHERE id = 13::bigint;$$);
SELECT isnt("validated_on_datetime", NULL) FROM gk_email_revalidate WHERE id = 13::bigint;
-- ip is mandatory
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (14, 1, 'test+1@geokrety.org', :TOKEN_UNUSED);
SELECT throws_ok($$UPDATE "gk_email_revalidate" SET "used" = 1::smallint, "validated_on_datetime" = '2020-04-07 04:00:00+00' WHERE id = 14::bigint;$$); -- 16
SELECT throws_ok($$UPDATE "gk_email_revalidate" SET "used" = 1::smallint WHERE id = 14::bigint;$$); -- 17

-- USED to DISABLED or EXPIRED does not require date and ip
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (15, 1, 'test+1@geokrety.org', :TOKEN_UNUSED);
UPDATE "gk_email_revalidate" SET "used" = :TOKEN_DISABLED, "validating_ip" = '127.0.0.1' WHERE id = 15::bigint; -- 18
SELECT is("validating_ip", NULL)         FROM gk_email_revalidate WHERE id = 15::bigint;
SELECT is("validated_on_datetime", NULL) FROM gk_email_revalidate WHERE id = 15::bigint;
UPDATE "gk_email_revalidate" SET "used" = :TOKEN_EXPIRED,  "validating_ip" = '127.0.0.1' WHERE id = 15::bigint;
SELECT is("validating_ip", NULL)         FROM gk_email_revalidate WHERE id = 15::bigint;
SELECT is("validated_on_datetime", NULL) FROM gk_email_revalidate WHERE id = 15::bigint;
UPDATE "gk_email_revalidate" SET "used" = :TOKEN_DISABLED, "validated_on_datetime" = '2020-04-07 04:00:00+00' WHERE id = 15::bigint;
SELECT is("validating_ip", NULL)         FROM gk_email_revalidate WHERE id = 15::bigint;
SELECT is("validated_on_datetime", NULL) FROM gk_email_revalidate WHERE id = 15::bigint;
UPDATE "gk_email_revalidate" SET "used" = :TOKEN_EXPIRED,  "validated_on_datetime" = '2020-04-07 04:00:00+00' WHERE id = 15::bigint;
SELECT is("validating_ip", NULL)         FROM gk_email_revalidate WHERE id = 15::bigint;
SELECT is("validated_on_datetime", NULL) FROM gk_email_revalidate WHERE id = 15::bigint;
UPDATE "gk_email_revalidate" SET "used" = :TOKEN_DISABLED, "validated_on_datetime" = '2020-04-07 04:00:00+00', "validating_ip" = '127.0.0.1' WHERE id = 15::bigint;
SELECT is("validating_ip", NULL)         FROM gk_email_revalidate WHERE id = 15::bigint;
SELECT is("validated_on_datetime", NULL) FROM gk_email_revalidate WHERE id = 15::bigint;
UPDATE "gk_email_revalidate" SET "used" = :TOKEN_EXPIRED,  "validated_on_datetime" = '2020-04-07 04:00:00+00', "validating_ip" = '127.0.0.1' WHERE id = 15::bigint;
SELECT is("validating_ip", NULL)         FROM gk_email_revalidate WHERE id = 15::bigint;
SELECT is("validated_on_datetime", NULL) FROM gk_email_revalidate WHERE id = 15::bigint;

-- do not interfere with other users
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (16, 1, 'test+1@geokrety.org', :TOKEN_UNUSED);
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "used") VALUES (17, 2, 'test+1@geokrety.org', :TOKEN_UNUSED);
SELECT is("used", :TOKEN_UNUSED) FROM gk_email_revalidate WHERE id = 16::bigint;
SELECT is("used", :TOKEN_UNUSED) FROM gk_email_revalidate WHERE id = 17::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
