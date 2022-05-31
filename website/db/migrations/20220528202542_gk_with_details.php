<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GkWithDetails extends AbstractMigration {
    public function up(): void {
        $sql = <<<'EOL'
SELECT gk_geokrety.id,
    gk_geokrety.gkid,
    gk_geokrety.tracking_code,
    gk_geokrety.name,
    gk_geokrety.mission,
    gk_geokrety.owner,
    gk_geokrety.distance,
    gk_geokrety.caches_count,
    gk_geokrety.pictures_count,
    gk_geokrety.last_position,
    gk_geokrety.last_log,
    gk_geokrety.holder,
    gk_geokrety.avatar,
    gk_geokrety.created_on_datetime,
    gk_geokrety.updated_on_datetime,
    gk_geokrety.missing,
    gk_geokrety.type,
    gk_moves."position",
    gk_moves.lat,
    gk_moves.lon,
    gk_moves.waypoint,
    gk_moves.elevation,
    gk_moves.country,
    gk_moves.move_type,
    gk_moves.author,
    gk_moves.moved_on_datetime,
    COALESCE(gk_moves.username, m_author.username) AS author_username,
    COALESCE(g_owner.username, 'Abandoned'::character varying) AS owner_username,
    g_avatar.key AS avatar_key,
    g_holder.username AS holder_username
FROM gk_geokrety
LEFT JOIN gk_moves ON gk_geokrety.last_position = gk_moves.id
LEFT JOIN gk_users m_author ON gk_moves.author = m_author.id
LEFT JOIN gk_users g_owner ON gk_geokrety.owner = g_owner.id
LEFT JOIN gk_users g_holder ON gk_geokrety.holder = g_holder.id
LEFT JOIN gk_pictures g_avatar ON gk_geokrety.avatar = g_avatar.id;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $sql = <<<'EOL'
SELECT gk_geokrety.id,
    gk_geokrety.gkid,
    gk_geokrety.tracking_code,
    gk_geokrety.name,
    gk_geokrety.mission,
    gk_geokrety.owner,
    gk_geokrety.distance,
    gk_geokrety.caches_count,
    gk_geokrety.pictures_count,
    gk_geokrety.last_position,
    gk_geokrety.last_log,
    gk_geokrety.holder,
    gk_geokrety.avatar,
    gk_geokrety.created_on_datetime,
    gk_geokrety.updated_on_datetime,
    gk_geokrety.missing,
    gk_geokrety.type,
    gk_moves."position",
    gk_moves.lat,
    gk_moves.lon,
    gk_moves.waypoint,
    gk_moves.elevation,
    gk_moves.country,
    gk_moves.move_type,
    gk_moves.author,
    gk_moves.moved_on_datetime,
    COALESCE(gk_moves.username, m_author.username) AS author_username,
    COALESCE(g_owner.username, 'Abandoned'::character varying) AS owner_username,
    g_avatar.key AS avatar_key
FROM gk_geokrety
LEFT JOIN gk_moves ON gk_geokrety.last_position = gk_moves.id
LEFT JOIN gk_users m_author ON gk_moves.author = m_author.id
LEFT JOIN gk_users g_owner ON gk_geokrety.owner = g_owner.id
LEFT JOIN gk_pictures g_avatar ON gk_geokrety.avatar = g_avatar.id;
EOL;
        $this->execute($sql);
    }
}
