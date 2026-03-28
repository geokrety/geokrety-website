BEGIN;

SELECT plan(6);

SELECT has_index(
    'geokrety',
    'gk_users',
    'idx_gk_users_joined_listing',
    'idx_gk_users_joined_listing exists'
);

SELECT ok(
    EXISTS (
        SELECT 1
        FROM pg_index AS i
        JOIN pg_class AS c ON c.oid = i.indexrelid
        JOIN pg_namespace AS n ON n.oid = c.relnamespace
        WHERE n.nspname = 'geokrety'
          AND c.relname = 'idx_gk_users_joined_listing'
          AND i.indisvalid
    ),
    'idx_gk_users_joined_listing is valid'
);

SELECT ok(
    pg_get_indexdef('geokrety.idx_gk_users_joined_listing'::regclass) LIKE '%(joined_on_datetime DESC, id DESC)%',
    'idx_gk_users_joined_listing stores joined_on_datetime DESC and id DESC'
);

SELECT ok(
    pg_get_indexdef('geokrety.idx_gk_users_joined_listing'::regclass) LIKE '%INCLUDE (username, home_country, avatar)%',
    'idx_gk_users_joined_listing covers username, home_country, and avatar'
);

CREATE OR REPLACE FUNCTION pg_temp.explain_plan_json(sql_text text)
RETURNS json
LANGUAGE plpgsql
AS $$
DECLARE
    plan json;
BEGIN
    EXECUTE sql_text INTO plan;
    RETURN plan;
END;
$$;

SET LOCAL enable_seqscan = off;

SELECT ok(
    (
        WITH plan_text AS (
            SELECT pg_temp.explain_plan_json(
                $$
                EXPLAIN (FORMAT JSON)
                WITH paged_users AS MATERIALIZED (
                    SELECT
                        u.id,
                        u.username,
                        u.joined_on_datetime AS joined_at,
                        UPPER(u.home_country) AS home_country,
                        u.avatar AS avatar_id
                    FROM geokrety.gk_users AS u
                    ORDER BY u.joined_on_datetime DESC, u.id DESC
                    LIMIT 21
                )
                SELECT
                    u.id,
                    u.username,
                    last_move.last_move_at
                FROM paged_users AS u
                LEFT JOIN LATERAL (
                    SELECT
                        m.moved_on_datetime AS last_move_at
                    FROM geokrety.gk_moves AS m
                    WHERE m.author = u.id
                    ORDER BY m.moved_on_datetime DESC
                    LIMIT 1
                ) AS last_move ON TRUE
                $$
            )::text AS p
        )
        SELECT p LIKE '%idx_gk_users_joined_listing%'
        FROM plan_text
    ),
    'planner uses idx_gk_users_joined_listing for joined-on ordering'
);

INSERT INTO gk_users (id, username, registration_ip, joined_on_datetime) VALUES (926500001, 'joined-order-old', '127.0.0.1', '2020-01-01 00:00:00+00');
INSERT INTO gk_users (id, username, registration_ip, joined_on_datetime) VALUES (926500002, 'joined-order-new', '127.0.0.1', '2020-01-02 00:00:00+00');

SELECT is(
    (
        SELECT array_agg(id ORDER BY joined_on_datetime DESC, id DESC)
        FROM geokrety.gk_users
        WHERE id IN (926500001, 926500002)
    ),
    ARRAY[926500002::bigint, 926500001::bigint],
    'joined-on ordering returns the most recently registered users first'
);

SELECT * FROM finish();
ROLLBACK;
