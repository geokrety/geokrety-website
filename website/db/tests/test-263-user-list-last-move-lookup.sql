BEGIN;

SELECT plan(5);

SELECT has_index(
    'geokrety',
    'gk_moves',
    'idx_gk_moves_author_last_move_lookup',
    'idx_gk_moves_author_last_move_lookup exists'
);

SELECT ok(
    EXISTS (
        SELECT 1
        FROM pg_index AS i
        JOIN pg_class AS c ON c.oid = i.indexrelid
        JOIN pg_namespace AS n ON n.oid = c.relnamespace
        WHERE n.nspname = 'geokrety'
          AND c.relname = 'idx_gk_moves_author_last_move_lookup'
          AND i.indisvalid
    ),
    'idx_gk_moves_author_last_move_lookup is valid'
);

SELECT ok(
    pg_get_indexdef('geokrety.idx_gk_moves_author_last_move_lookup'::regclass) LIKE '%(author, moved_on_datetime DESC)%',
    'idx_gk_moves_author_last_move_lookup stores author and moved_on_datetime DESC'
);

SELECT ok(
    pg_get_expr(
        (
            SELECT i.indpred
            FROM pg_index AS i
            WHERE i.indexrelid = 'geokrety.idx_gk_moves_author_last_move_lookup'::regclass
        ),
        'geokrety.gk_moves'::regclass
    ) LIKE '%author IS NOT NULL%',
    'idx_gk_moves_author_last_move_lookup is partial on author IS NOT NULL'
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
SET LOCAL enable_bitmapscan = off;

SELECT ok(
    (
        WITH plan_text AS (
            SELECT pg_temp.explain_plan_json(
                $$
                EXPLAIN (FORMAT JSON)
                SELECT
                    u.id,
                    u.username,
                    last_move.last_move_at
                FROM geokrety.gk_users AS u
                LEFT JOIN LATERAL (
                    SELECT
                        m.moved_on_datetime AS last_move_at
                    FROM geokrety.gk_moves AS m
                    WHERE m.author = u.id
                    ORDER BY m.moved_on_datetime DESC
                    LIMIT 1
                ) AS last_move ON TRUE
                ORDER BY u.username ASC, u.id ASC
                LIMIT 21
                $$
            )::text AS p
        )
        SELECT
            p LIKE '%Index%Scan%' AND p NOT LIKE '%Seq Scan%'
        FROM plan_text
    ),
    'planner uses an index scan (no seq scan) for user list last_move_at lookups'
);

SELECT * FROM finish();

ROLLBACK;
