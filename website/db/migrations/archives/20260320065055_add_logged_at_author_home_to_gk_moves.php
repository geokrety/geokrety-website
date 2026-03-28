<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLoggedAtAuthorHomeToGkMoves extends AbstractMigration {
    public function up(): void {
        $this->table('gk_moves', ['schema' => 'geokrety'])
            ->addColumn('logged_at_author_home', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->update();

        $this->execute(<<<'SQL'
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_trigger
    WHERE tgname = 'before_20_gis_updates'
      AND tgrelid = 'geokrety.gk_moves'::regclass
  ) THEN
    RAISE EXCEPTION 'Required trigger before_20_gis_updates not found on geokrety.gk_moves. Check that geokrety-schema migrations have been applied.';
  END IF;
END;
$$;

-- Match against the normalized geography only. The business predicate is
-- ST_DWithin(..., 50) on NEW.position and gk_users.home_position.
CREATE FUNCTION geokrety.fn_gk_moves_set_logged_at_author_home()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_home_position public.geography;
BEGIN
  IF TG_OP = 'UPDATE'
     AND NEW.author IS NOT DISTINCT FROM OLD.author
     AND NEW.position IS NOT DISTINCT FROM OLD.position
     AND NEW.logged_at_author_home IS NOT DISTINCT FROM OLD.logged_at_author_home THEN
    RETURN NEW;
  END IF;

  NEW.logged_at_author_home := false;

  IF NEW.author IS NULL OR NEW.position IS NULL THEN
    RETURN NEW;
  END IF;

  SELECT u.home_position
  INTO v_home_position
  FROM geokrety.gk_users u
  WHERE u.id = NEW.author;

  IF v_home_position IS NULL THEN
    RETURN NEW;
  END IF;

  NEW.logged_at_author_home := public.ST_DWithin(
    NEW.position,
    v_home_position,
    50
  );

  RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_moves_before_logged_at_author_home ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_before_logged_at_author_home
  BEFORE INSERT OR UPDATE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_set_logged_at_author_home();

-- Historical repair remains manual on purpose. Each invocation updates at most
-- one deterministic ascending-id batch whose derived value differs, so callers
-- can commit between batches during large repair runs.
-- Direct SQL calls perform ordinary UPDATEs on geokrety.gk_moves. The
-- standalone maintenance CLI may run these batches under replica role to
-- suppress unrelated trigger side effects during a controlled repair window.
CREATE FUNCTION stats.fn_backfill_gk_moves_logged_at_author_home(
  p_period tstzrange DEFAULT NULL,
  p_batch_size integer DEFAULT 50000
)
RETURNS text
LANGUAGE plpgsql
AS $$
DECLARE
  v_batch_processed INTEGER := 0;
  v_batch_updated INTEGER := 0;
  v_batch_count INTEGER := 0;
  v_scope_description TEXT;
  v_scope_has_rows BOOLEAN := false;
BEGIN
  IF p_batch_size IS NULL OR p_batch_size <= 0 THEN
    RAISE EXCEPTION 'p_batch_size must be a positive integer (got %)', p_batch_size
      USING HINT = 'Use DEFAULT value for automatic batch sizing or provide p_batch_size > 0';
  END IF;

  IF p_period IS NOT NULL THEN
    SELECT EXISTS (
      SELECT 1
      FROM geokrety.gk_moves m
      WHERE p_period @> m.moved_on_datetime
    )
    INTO v_scope_has_rows;
  END IF;

  WITH derived_rows AS (
      SELECT
        m.id,
        m.logged_at_author_home,
        CASE
          WHEN m.author IS NULL OR m.position IS NULL OR u.home_position IS NULL THEN false
          ELSE public.ST_DWithin(m.position, u.home_position, 50)
        END AS derived_value
      FROM geokrety.gk_moves m
      LEFT JOIN geokrety.gk_users u
        ON u.id = m.author
      WHERE p_period IS NULL OR p_period @> m.moved_on_datetime
    ),
    candidate_rows AS (
      SELECT
        d.id,
        d.derived_value
      FROM derived_rows d
      WHERE d.logged_at_author_home IS DISTINCT FROM d.derived_value
      ORDER BY d.id
      LIMIT p_batch_size
    ),
    updated_rows AS (
      UPDATE geokrety.gk_moves m
      SET logged_at_author_home = c.derived_value
      FROM candidate_rows c
      WHERE m.id = c.id
        AND m.logged_at_author_home IS DISTINCT FROM c.derived_value
      RETURNING m.id
    )
    SELECT
      COUNT(c.id),
      COALESCE((SELECT COUNT(*) FROM updated_rows), 0)
    INTO v_batch_processed, v_batch_updated
    FROM candidate_rows c;

  IF v_batch_processed > 0 THEN
    v_batch_count := 1;
  END IF;

  IF p_period IS NULL THEN
    v_scope_description := 'full-history scope';
  ELSIF NOT v_scope_has_rows THEN
    v_scope_description := 'empty period scope (no rows in range)';
  ELSE
    v_scope_description := format(
      'period-scoped from %s to %s',
      COALESCE(to_char(lower(p_period) AT TIME ZONE 'UTC', 'YYYY-MM-DD'), 'unbounded'),
      COALESCE(to_char(upper(p_period) AT TIME ZONE 'UTC', 'YYYY-MM-DD'), 'unbounded')
    );
  END IF;

  RETURN format(
    'Processed %s rows; %s rows updated; %s batches completed; %s.',
    v_batch_processed,
    v_batch_updated,
    v_batch_count,
    v_scope_description
  );
END;
$$;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP TRIGGER IF EXISTS tr_gk_moves_before_logged_at_author_home ON geokrety.gk_moves;
DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_set_logged_at_author_home();
DROP FUNCTION IF EXISTS stats.fn_backfill_gk_moves_logged_at_author_home(tstzrange, integer);
SQL
        );

        $this->table('gk_moves', ['schema' => 'geokrety'])
            ->removeColumn('logged_at_author_home')
            ->update();
    }
}
