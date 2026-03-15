BEGIN;
SELECT plan(5);

SELECT has_index('geokrety', 'gk_moves', 'gk_moves_pkey', 'gk_moves primary key was renamed to gk_moves_pkey');
SELECT ok(
  (
    SELECT COUNT(*) = 0
    FROM pg_indexes
    WHERE schemaname = 'geokrety'
      AND tablename = 'gk_moves'
      AND indexname = ANY (ARRAY[
        'idx_21044_lat',
        'idx_21044_lon',
        'idx_21044_data_dodania',
        'idx_21044_timestamp',
        'idx_21044_alt',
        'idx_21044_data',
        'idx_21044_waypoint',
        'idx_21044_user'
      ])
  ),
  'legacy idx_21044 duplicates were removed'
);
SELECT ok(
  (
    SELECT COUNT(*) = 0
    FROM pg_indexes
    WHERE schemaname = 'geokrety'
      AND tablename = 'gk_moves'
      AND indexname = ANY (ARRAY[
        'gk_moves_country_index',
        'gk_moves_type_index',
        'idx_moves_geokret',
        'idx_moves_id'
      ])
  ),
  'single-column duplicate gk_moves indexes were removed'
);
SELECT ok(
  (
    SELECT COUNT(*) = 0
    FROM pg_indexes
    WHERE schemaname = 'geokrety'
      AND tablename = 'gk_moves'
      AND indexname = ANY (ARRAY[
        'gk_moves_move_type_id',
        'idx_moves_type_id',
        'gk_moves_move_type_id_position'
      ])
  ),
  'multi-column duplicate gk_moves indexes were removed'
);
SELECT ok(
  (
    SELECT COUNT(*) = 3
    FROM pg_indexes
    WHERE schemaname = 'geokrety'
      AND tablename = 'gk_moves'
      AND indexname = ANY (ARRAY[
        'idx_gk_moves_prev_loc_lookup',
        'idx_gk_moves_geokret_chainlookup',
        'idx_gk_moves_qualified_period'
      ])
  ),
  'critical backfill and runtime indexes remain present'
);

SELECT * FROM finish();
ROLLBACK;
