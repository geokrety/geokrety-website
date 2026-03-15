-- Test: gk_moves new source columns and FK
BEGIN;
SELECT plan(8);

SELECT has_column('geokrety', 'gk_moves', 'previous_move_id', 'previous_move_id column exists');
SELECT has_column('geokrety', 'gk_moves', 'previous_position_id', 'previous_position_id column exists');
SELECT col_type_is('geokrety', 'gk_moves', 'previous_position_id', 'bigint', 'previous_position_id has correct type');
SELECT col_type_is('geokrety', 'gk_moves', 'km_distance', 'numeric(8,3)', 'km_distance has correct type');
SELECT ok(EXISTS(SELECT 1 FROM pg_constraint WHERE conname = 'fk_gk_moves_previous_move' AND conrelid = 'geokrety.gk_moves'::regclass), 'previous_move_id FK exists');
SELECT ok((SELECT condeferrable FROM pg_constraint WHERE conname = 'fk_gk_moves_previous_move' AND conrelid = 'geokrety.gk_moves'::regclass), 'previous_move_id FK is DEFERRABLE');
SELECT ok(EXISTS(SELECT 1 FROM pg_constraint WHERE conname = 'fk_gk_moves_previous_position' AND conrelid = 'geokrety.gk_moves'::regclass), 'previous_position_id FK exists');
SELECT ok((SELECT condeferrable FROM pg_constraint WHERE conname = 'fk_gk_moves_previous_position' AND conrelid = 'geokrety.gk_moves'::regclass), 'previous_position_id FK is DEFERRABLE');

SELECT * FROM finish();
ROLLBACK;
