<?php

namespace GeoKrety\Controller\API\v1\Geokrety;

use GeoKrety\Controller\API\BaseJson;
use GeoKrety\LogType;
use GeoKrety\Model\Move;
use GeoKrety\Traits\GeokretLoader;

class Statistics extends BaseJson {
    use GeokretLoader;

    public function elevation_profile() {
        $movesLoader = new Move();
        $options = ['order' => 'moved_on_datetime'];
        $move_type = LogType::LOG_TYPES_REQUIRING_COORDINATES;
        $moves = $movesLoader->find(['geokret = ? AND move_type IN ?', $this->geokret->id, $move_type], $options);
        $response = [];
        foreach ($moves ?: [] as $move) {
            $response[] = $move;
        }
        echo json_encode($response);
    }

    public function countries() {
        $db = \Base::instance()->get('DB');
        $sql = <<<'SQL'
SELECT lower(country) AS country,
       COUNT(*) AS move_count,
       COUNT(DISTINCT author) AS mover_count,
       MAX(moved_on_datetime) AS last_moved_on_datetime
FROM gk_moves
WHERE geokret = ?
AND country IS NOT NULL
SQL;

        $moveTypes = LogType::LOG_TYPES_REQUIRING_COORDINATES;
        $params = [$this->geokret->id];

        $placeholders = implode(',', array_fill(0, count($moveTypes), '?'));
        $sql .= ' AND move_type IN ('.$placeholders.')';
        $params = array_merge($params, $moveTypes);
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
