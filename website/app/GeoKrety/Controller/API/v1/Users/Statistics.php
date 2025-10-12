<?php

namespace GeoKrety\Controller\API\v1\Users;

use GeoKrety\Controller\API\BaseJson;
use GeoKrety\LogType;
use GeoKrety\Traits\UserLoader;

class Statistics extends BaseJson {
    use UserLoader;

    public function countries() {
        $db = \Base::instance()->get('DB');
        $moveTypes = LogType::LOG_TYPES_REQUIRING_COORDINATES;
        $params = [$this->user->id];
        $sql = <<<'SQL'
SELECT lower(country) AS country,
       COUNT(*) AS move_count,
       COUNT(DISTINCT geokret) AS mover_count,
       MAX(moved_on_datetime) AS last_moved_on_datetime
FROM gk_moves
WHERE author = ?
AND country IS NOT NULL
SQL;

        if (!empty($moveTypes)) {
            $placeholders = implode(',', array_fill(0, count($moveTypes), '?'));
            $sql .= ' AND move_type IN ('.$placeholders.')';
            $params = array_merge($params, $moveTypes);
        }

        $sql .= ' GROUP BY lower(country) ORDER BY move_count DESC, last_moved_on_datetime DESC';

        $rows = $db->exec($sql, $params) ?: [];

        $response = array_map(static function (array $row): array {
            $lastMove = $row['last_moved_on_datetime'] ?? null;
            if (!is_null($lastMove)) {
                try {
                    $lastMove = (new \DateTime($lastMove))->format('c');
                } catch (\Exception) {
                    $lastMove = null;
                }
            }

            return [
                'country' => $row['country'],
                'move_count' => (int) ($row['move_count'] ?? 0),
                'mover_count' => (int) ($row['mover_count'] ?? 0),
                'last_moved_on_datetime' => $lastMove,
            ];
        }, $rows);

        echo json_encode($response);
    }
}
