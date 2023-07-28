-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(23);


-- Empty data
SELECT is(COUNT(*), 0::bigint) FROM geokrety.gk_users_authentication_history;
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foobar');

-- Insert one failed attempt
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:00+00', NULL, 'foobar', 'Some User Agent', '127.0.0.1', FALSE, '123456789abcdef', NULL, 'password');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo2');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo');
SELECT is(COUNT(*), 1::bigint) FROM geokrety.previous_failed_logins('foobar');

-- Insert a second failed attempt
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:01+00', NULL, 'foobar', 'Some User Agent', '127.0.0.1', FALSE, '123456789abcdef', NULL, 'password');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo2');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo');
SELECT is(COUNT(*), 2::bigint) FROM geokrety.previous_failed_logins('foobar');

-- Insert a valid attempt
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:02+00', NULL, 'foobar', 'Some User Agent', '127.0.0.1', TRUE, '123456789abcdef', NULL, 'password');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo2');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo');
SELECT is(COUNT(*), 2::bigint) FROM geokrety.previous_failed_logins('foobar');

-- Another user connected
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:03+00', NULL, 'foo', 'Some User Agent', '127.0.0.1', TRUE, '123456789abcdef', NULL, 'password');
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:04+00', NULL, 'foo2', 'Some User Agent', '127.0.0.1', FALSE, '123456789abcdef', NULL, 'password');
SELECT is(COUNT(*), 1::bigint) FROM geokrety.previous_failed_logins('foo2');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo');
SELECT is(COUNT(*), 2::bigint) FROM geokrety.previous_failed_logins('foobar');

-- Insert another valid attempt
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:05+00', NULL, 'foobar', 'Some User Agent', '127.0.0.1', TRUE, '123456789abcdef', NULL, 'password');
SELECT is(COUNT(*), 1::bigint) FROM geokrety.previous_failed_logins('foo2');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foobar');

-- beetween 2 valid attempts
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:07+00', NULL, 'foobar', 'Some User Agent', '127.0.0.1', FALSE, '123456789abcdef', NULL, 'password');
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:08+00', NULL, 'foobar', 'Some User Agent', '127.0.0.1', TRUE, '123456789abcdef', NULL, 'password');
SELECT is(COUNT(*), 1::bigint) FROM geokrety.previous_failed_logins('foo2');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo');
SELECT is(COUNT(*), 1::bigint) FROM geokrety.previous_failed_logins('foobar');

-- two consecutive valid attempts
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:09+00', NULL, 'foo2', 'Some User Agent', '127.0.0.1', TRUE, '123456789abcdef', NULL, 'password');
SELECT is(COUNT(*), 1::bigint) FROM geokrety.previous_failed_logins('foo2');
INSERT INTO geokrety.gk_users_authentication_history ("created_on_datetime", "user", "username", "user_agent", "ip", "succeed", "session", "comment", "method")
VALUES ('2023-07-29 00:00:10+00', NULL, 'foo2', 'Some User Agent', '127.0.0.1', TRUE, '123456789abcdef', NULL, 'password');
SELECT is(COUNT(*), 0::bigint) FROM geokrety.previous_failed_logins('foo2');


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
