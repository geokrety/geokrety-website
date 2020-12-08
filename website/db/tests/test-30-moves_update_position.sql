-- Start transaction and plan the tests.

BEGIN;
SELECT plan(4);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

INSERT INTO "gk_geokrety"
    ("id", "gkid", "tracking_code", "name", "mission", "owner", "distance", "caches_count", "pictures_count", "last_position",
     "last_log", "holder", "avatar", "created_on_datetime", "updated_on_datetime", "missing", "type")
VALUES (1, 1, 'ABC123', 'test 1', NULL, NULL, '0', '0', '0', NULL, NULL, NULL, NULL, '2020-04-07 00:00:00+00', now(), '0', '0');

SELECT is(id, 1::bigint, 'Seed a GeoKret') from gk_geokrety WHERE id = 1::bigint;


INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (1, 1, 1, '43.6', '6.8', '2020-04-07 01:00:00+00', '0');

SELECT is(
    position,
    '0101000020E61000003333333333331B40CDCCCCCCCCCC4540',
    'Position is automatically synced'
) from gk_moves WHERE id = 1;


INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "position")
VALUES (2, 1, 1, '2020-04-07 02:00:00+00', '0', '0101000020E61000003333333333331B40CDCCCCCCCCCC4540');

SELECT results_eq(
    'SELECT lat, lon FROM gk_moves WHERE id = 2::bigint',
    $$VALUES (43.6::double precision , 6.8::double precision )$$,
    'Lat/Lon are automatically synced'
);


UPDATE "gk_moves" SET lat=46.2, lon=6.1 WHERE id=1;

SELECT is(
    position,
    '0101000020E610000066666666666618409A99999999194740',
    'Position is automatically updated'
) from gk_moves WHERE id = 1;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
