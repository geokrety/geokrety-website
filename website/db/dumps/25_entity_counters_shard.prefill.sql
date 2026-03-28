TRUNCATE TABLE stats.entity_counters_shard;
INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
SELECT entities.entity, shards.shard, 0
FROM (
  VALUES
    ('gk_moves'), ('gk_moves_type_0'), ('gk_moves_type_1'), ('gk_moves_type_2'),
    ('gk_moves_type_3'), ('gk_moves_type_4'), ('gk_moves_type_5'),
    ('gk_geokrety'), ('gk_geokrety_type_0'), ('gk_geokrety_type_1'),
    ('gk_geokrety_type_2'), ('gk_geokrety_type_3'), ('gk_geokrety_type_4'),
    ('gk_geokrety_type_5'), ('gk_geokrety_type_6'), ('gk_geokrety_type_7'),
    ('gk_geokrety_type_8'), ('gk_geokrety_type_9'), ('gk_geokrety_type_10'),
    ('gk_pictures'), ('gk_pictures_type_0'), ('gk_pictures_type_1'),
    ('gk_pictures_type_2'), ('gk_users'), ('gk_loves')
) AS entities(entity)
CROSS JOIN generate_series(0,15) AS shards(shard);

REFRESH MATERIALIZED VIEW geokrety.gk_geokrety_in_caches;
