-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(9);

SELECT has_schema('audit');

SELECT has_table( 'audit'::name, 'actions_logs'::name );
SELECT has_table( 'audit'::name, 'posts'::name );

SELECT tables_are(
    'audit',
    ARRAY[ 'actions_logs', 'posts' ]
);

INSERT INTO "audit"."actions_logs" ("id", "log_datetime", "event", "author", "ip", "context", "session") VALUES (1::bigint, '2022-06-19 09:18:54', 'test', 1234, '192.168.0.1', '{"username": "foo"}', 'abcdef123456');
SELECT is("event", 'test') FROM "audit"."actions_logs" WHERE id = 1::bigint;
SELECT is("author", 1234::bigint) FROM "audit"."actions_logs" WHERE id = 1::bigint;

INSERT INTO "audit"."posts" ("id", "author", "ip", "route", "payload", "created_on_datetime", "session") VALUES (1::bigint, '1234', '192.168.0.1', '/test', '{"username": "foo"}', '2022-06-19 09:18:54', 'abcdef123456');
SELECT is("route", '/test') FROM "audit"."posts" WHERE id = 1::bigint;
SELECT is("errors"::jsonb, NULL) FROM "audit"."posts" WHERE id = 1::bigint;

UPDATE "audit"."posts" SET "errors" = '{"foo": "bar"}' WHERE id = 1::bigint;
SELECT is("errors"::jsonb, '{"foo": "bar"}'::jsonb) FROM "audit"."posts" WHERE id = 1::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
