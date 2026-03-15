BEGIN;
SELECT plan(1);

SELECT ok(EXISTS(SELECT 1 FROM pg_extension WHERE extname = 'btree_gist'), 'btree_gist extension is installed');

SELECT * FROM finish();
ROLLBACK;
