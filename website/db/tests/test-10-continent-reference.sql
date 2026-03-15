BEGIN;
SELECT plan(8);

SELECT has_table('stats', 'continent_reference', 'stats.continent_reference table exists');
SELECT has_column('stats', 'continent_reference', 'country_alpha2', 'country_alpha2 column exists');
SELECT has_column('stats', 'continent_reference', 'continent_code', 'continent_code column exists');
SELECT is((SELECT count(*) FROM stats.continent_reference), 249::bigint, 'continent_reference is seeded with 249 rows');
SELECT is((SELECT count(DISTINCT continent_code) FROM stats.continent_reference), 7::bigint, 'continent_reference covers all seven continents');
SELECT is((SELECT continent_code FROM stats.continent_reference WHERE country_alpha2 = 'PL'), 'EU', 'PL maps to Europe');
SELECT is((SELECT continent_code FROM stats.continent_reference WHERE country_alpha2 = 'US'), 'NA', 'US maps to North America');
SELECT is((SELECT continent_code FROM stats.continent_reference WHERE country_alpha2 = 'JP'), 'AS', 'JP maps to Asia');

SELECT * FROM finish();
ROLLBACK;
