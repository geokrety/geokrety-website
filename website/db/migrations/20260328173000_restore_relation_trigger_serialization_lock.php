<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RestoreRelationTriggerSerializationLock extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_relations()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_geokrety_ids INT[] := ARRAY[]::INT[];
  v_user_ids INT[] := ARRAY[]::INT[];
  v_new_relation BOOLEAN := FALSE;
  v_related RECORD;
BEGIN
  IF TG_OP = 'INSERT' THEN
    IF NEW.author IS NULL OR NEW.move_type NOT IN (0, 1, 3, 5) THEN
      RETURN NEW;
    END IF;

    SELECT NOT EXISTS (
      SELECT 1
      FROM stats.gk_related_users gru
      WHERE gru.geokrety_id = NEW.geokret
        AND gru.user_id = NEW.author
    )
    INTO v_new_relation;

    INSERT INTO stats.gk_related_users (
      geokrety_id,
      user_id,
      interaction_count,
      first_interaction,
      last_interaction
    )
    VALUES (
      NEW.geokret,
      NEW.author,
      1,
      NEW.moved_on_datetime,
      NEW.moved_on_datetime
    )
    ON CONFLICT (geokrety_id, user_id) DO UPDATE
    SET interaction_count = stats.gk_related_users.interaction_count + 1,
        first_interaction = LEAST(stats.gk_related_users.first_interaction, EXCLUDED.first_interaction),
        last_interaction = GREATEST(stats.gk_related_users.last_interaction, EXCLUDED.last_interaction);

    FOR v_related IN
      SELECT gru.user_id, gru.first_interaction, gru.last_interaction
      FROM stats.gk_related_users gru
      WHERE gru.geokrety_id = NEW.geokret
        AND gru.user_id <> NEW.author
    LOOP
      INSERT INTO stats.user_related_users (
        user_id,
        related_user_id,
        shared_geokrety_count,
        first_seen_at,
        last_seen_at
      )
      VALUES (
        NEW.author,
        v_related.user_id,
        1,
        LEAST(NEW.moved_on_datetime, v_related.first_interaction),
        GREATEST(NEW.moved_on_datetime, v_related.last_interaction)
      )
      ON CONFLICT (user_id, related_user_id) DO UPDATE
      SET shared_geokrety_count = stats.user_related_users.shared_geokrety_count + CASE WHEN v_new_relation THEN 1 ELSE 0 END,
          first_seen_at = LEAST(stats.user_related_users.first_seen_at, EXCLUDED.first_seen_at),
          last_seen_at = GREATEST(stats.user_related_users.last_seen_at, EXCLUDED.last_seen_at);

      INSERT INTO stats.user_related_users (
        user_id,
        related_user_id,
        shared_geokrety_count,
        first_seen_at,
        last_seen_at
      )
      VALUES (
        v_related.user_id,
        NEW.author,
        1,
        LEAST(NEW.moved_on_datetime, v_related.first_interaction),
        GREATEST(NEW.moved_on_datetime, v_related.last_interaction)
      )
      ON CONFLICT (user_id, related_user_id) DO UPDATE
      SET shared_geokrety_count = stats.user_related_users.shared_geokrety_count + CASE WHEN v_new_relation THEN 1 ELSE 0 END,
          first_seen_at = LEAST(stats.user_related_users.first_seen_at, EXCLUDED.first_seen_at),
          last_seen_at = GREATEST(stats.user_related_users.last_seen_at, EXCLUDED.last_seen_at);
    END LOOP;

    RETURN NEW;
  END IF;

  IF TG_OP IN ('UPDATE', 'DELETE')
     AND OLD.author IS NOT NULL
     AND OLD.move_type IN (0, 1, 3, 5) THEN
    v_geokrety_ids := array_append(v_geokrety_ids, OLD.geokret);
    v_user_ids := array_append(v_user_ids, OLD.author);
  END IF;

  IF TG_OP = 'UPDATE'
     AND NEW.author IS NOT NULL
     AND NEW.move_type IN (0, 1, 3, 5) THEN
    v_geokrety_ids := array_append(v_geokrety_ids, NEW.geokret);
    v_user_ids := array_append(v_user_ids, NEW.author);
  END IF;

  SELECT array_agg(DISTINCT geokrety_id)
    INTO v_geokrety_ids
  FROM unnest(v_geokrety_ids) AS affected_geokrety(geokrety_id);

  IF v_geokrety_ids IS NULL OR cardinality(v_geokrety_ids) = 0 THEN
    RETURN COALESCE(NEW, OLD);
  END IF;

  PERFORM pg_advisory_xact_lock(20260321, 1);

  DELETE FROM stats.gk_related_users
  WHERE geokrety_id = ANY(v_geokrety_ids);

  INSERT INTO stats.gk_related_users (
    geokrety_id,
    user_id,
    interaction_count,
    first_interaction,
    last_interaction
  )
  SELECT
    m.geokret,
    m.author,
    COUNT(*)::BIGINT,
    MIN(m.moved_on_datetime),
    MAX(m.moved_on_datetime)
  FROM geokrety.gk_moves m
  WHERE m.geokret = ANY(v_geokrety_ids)
    AND m.author IS NOT NULL
    AND m.move_type IN (0, 1, 3, 5)
  GROUP BY m.geokret, m.author;

  SELECT array_agg(DISTINCT user_id)
    INTO v_user_ids
  FROM (
    SELECT unnest(v_user_ids) AS user_id
    UNION
    SELECT gru.user_id
    FROM stats.gk_related_users gru
    WHERE gru.geokrety_id = ANY(v_geokrety_ids)
  ) AS affected_users;

  IF v_user_ids IS NOT NULL AND cardinality(v_user_ids) > 0 THEN
    DELETE FROM stats.user_related_users
    WHERE user_id = ANY(v_user_ids)
       OR related_user_id = ANY(v_user_ids);

    INSERT INTO stats.user_related_users (
      user_id,
      related_user_id,
      shared_geokrety_count,
      first_seen_at,
      last_seen_at
    )
    SELECT
      left_side.user_id,
      right_side.user_id AS related_user_id,
      COUNT(DISTINCT left_side.geokrety_id)::BIGINT,
      MIN(LEAST(left_side.first_interaction, right_side.first_interaction)),
      MAX(GREATEST(left_side.last_interaction, right_side.last_interaction))
    FROM stats.gk_related_users left_side
    JOIN stats.gk_related_users right_side
      ON right_side.geokrety_id = left_side.geokrety_id
     AND right_side.user_id <> left_side.user_id
    WHERE left_side.user_id = ANY(v_user_ids)
       OR right_side.user_id = ANY(v_user_ids)
    GROUP BY left_side.user_id, right_side.user_id;
  END IF;

  RETURN COALESCE(NEW, OLD);
END;
$$;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_relations()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_geokrety_ids INT[] := ARRAY[]::INT[];
  v_user_ids INT[] := ARRAY[]::INT[];
  v_new_relation BOOLEAN := FALSE;
  v_related RECORD;
BEGIN
  IF TG_OP = 'INSERT' THEN
    IF NEW.author IS NULL OR NEW.move_type NOT IN (0, 1, 3, 5) THEN
      RETURN NEW;
    END IF;

    SELECT NOT EXISTS (
      SELECT 1
      FROM stats.gk_related_users gru
      WHERE gru.geokrety_id = NEW.geokret
        AND gru.user_id = NEW.author
    )
    INTO v_new_relation;

    INSERT INTO stats.gk_related_users (
      geokrety_id,
      user_id,
      interaction_count,
      first_interaction,
      last_interaction
    )
    VALUES (
      NEW.geokret,
      NEW.author,
      1,
      NEW.moved_on_datetime,
      NEW.moved_on_datetime
    )
    ON CONFLICT (geokrety_id, user_id) DO UPDATE
    SET interaction_count = stats.gk_related_users.interaction_count + 1,
        first_interaction = LEAST(stats.gk_related_users.first_interaction, EXCLUDED.first_interaction),
        last_interaction = GREATEST(stats.gk_related_users.last_interaction, EXCLUDED.last_interaction);

    FOR v_related IN
      SELECT gru.user_id, gru.first_interaction, gru.last_interaction
      FROM stats.gk_related_users gru
      WHERE gru.geokrety_id = NEW.geokret
        AND gru.user_id <> NEW.author
    LOOP
      INSERT INTO stats.user_related_users (
        user_id,
        related_user_id,
        shared_geokrety_count,
        first_seen_at,
        last_seen_at
      )
      VALUES (
        NEW.author,
        v_related.user_id,
        1,
        LEAST(NEW.moved_on_datetime, v_related.first_interaction),
        GREATEST(NEW.moved_on_datetime, v_related.last_interaction)
      )
      ON CONFLICT (user_id, related_user_id) DO UPDATE
      SET shared_geokrety_count = stats.user_related_users.shared_geokrety_count + CASE WHEN v_new_relation THEN 1 ELSE 0 END,
          first_seen_at = LEAST(stats.user_related_users.first_seen_at, EXCLUDED.first_seen_at),
          last_seen_at = GREATEST(stats.user_related_users.last_seen_at, EXCLUDED.last_seen_at);

      INSERT INTO stats.user_related_users (
        user_id,
        related_user_id,
        shared_geokrety_count,
        first_seen_at,
        last_seen_at
      )
      VALUES (
        v_related.user_id,
        NEW.author,
        1,
        LEAST(NEW.moved_on_datetime, v_related.first_interaction),
        GREATEST(NEW.moved_on_datetime, v_related.last_interaction)
      )
      ON CONFLICT (user_id, related_user_id) DO UPDATE
      SET shared_geokrety_count = stats.user_related_users.shared_geokrety_count + CASE WHEN v_new_relation THEN 1 ELSE 0 END,
          first_seen_at = LEAST(stats.user_related_users.first_seen_at, EXCLUDED.first_seen_at),
          last_seen_at = GREATEST(stats.user_related_users.last_seen_at, EXCLUDED.last_seen_at);
    END LOOP;

    RETURN NEW;
  END IF;

  IF TG_OP IN ('UPDATE', 'DELETE')
     AND OLD.author IS NOT NULL
     AND OLD.move_type IN (0, 1, 3, 5) THEN
    v_geokrety_ids := array_append(v_geokrety_ids, OLD.geokret);
    v_user_ids := array_append(v_user_ids, OLD.author);
  END IF;

  IF TG_OP = 'UPDATE'
     AND NEW.author IS NOT NULL
     AND NEW.move_type IN (0, 1, 3, 5) THEN
    v_geokrety_ids := array_append(v_geokrety_ids, NEW.geokret);
    v_user_ids := array_append(v_user_ids, NEW.author);
  END IF;

  SELECT array_agg(DISTINCT geokrety_id)
    INTO v_geokrety_ids
  FROM unnest(v_geokrety_ids) AS affected_geokrety(geokrety_id);

  IF v_geokrety_ids IS NULL OR cardinality(v_geokrety_ids) = 0 THEN
    RETURN COALESCE(NEW, OLD);
  END IF;

  DELETE FROM stats.gk_related_users
  WHERE geokrety_id = ANY(v_geokrety_ids);

  INSERT INTO stats.gk_related_users (
    geokrety_id,
    user_id,
    interaction_count,
    first_interaction,
    last_interaction
  )
  SELECT
    m.geokret,
    m.author,
    COUNT(*)::BIGINT,
    MIN(m.moved_on_datetime),
    MAX(m.moved_on_datetime)
  FROM geokrety.gk_moves m
  WHERE m.geokret = ANY(v_geokrety_ids)
    AND m.author IS NOT NULL
    AND m.move_type IN (0, 1, 3, 5)
  GROUP BY m.geokret, m.author;

  SELECT array_agg(DISTINCT user_id)
    INTO v_user_ids
  FROM (
    SELECT unnest(v_user_ids) AS user_id
    UNION
    SELECT gru.user_id
    FROM stats.gk_related_users gru
    WHERE gru.geokrety_id = ANY(v_geokrety_ids)
  ) AS affected_users;

  IF v_user_ids IS NOT NULL AND cardinality(v_user_ids) > 0 THEN
    DELETE FROM stats.user_related_users
    WHERE user_id = ANY(v_user_ids)
       OR related_user_id = ANY(v_user_ids);

    INSERT INTO stats.user_related_users (
      user_id,
      related_user_id,
      shared_geokrety_count,
      first_seen_at,
      last_seen_at
    )
    SELECT
      left_side.user_id,
      right_side.user_id AS related_user_id,
      COUNT(DISTINCT left_side.geokrety_id)::BIGINT,
      MIN(LEAST(left_side.first_interaction, right_side.first_interaction)),
      MAX(GREATEST(left_side.last_interaction, right_side.last_interaction))
    FROM stats.gk_related_users left_side
    JOIN stats.gk_related_users right_side
      ON right_side.geokrety_id = left_side.geokrety_id
     AND right_side.user_id <> left_side.user_id
    WHERE left_side.user_id = ANY(v_user_ids)
       OR right_side.user_id = ANY(v_user_ids)
    GROUP BY left_side.user_id, right_side.user_id;
  END IF;

  RETURN COALESCE(NEW, OLD);
END;
$$;
SQL
        );
    }
}
