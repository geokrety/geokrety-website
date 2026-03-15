-- Test: stats operational tables (backfill_progress, job_log)
BEGIN;
SELECT plan(5);

SELECT has_table('stats', 'backfill_progress', 'stats.backfill_progress table exists');
SELECT has_table('stats', 'job_log', 'stats.job_log table exists');

SELECT has_column('stats', 'backfill_progress', 'cursor_id', 'backfill_progress.cursor_id exists');
SELECT has_column('stats', 'backfill_progress', 'status', 'backfill_progress.status exists');
SELECT has_column('stats', 'job_log', 'metadata', 'job_log.metadata exists');

SELECT * FROM finish();
ROLLBACK;
