<?php

namespace GeoKrety\Controller\API\v1;

use GeoKrety\Controller\API\BaseJson;
use GeoKrety\LogType;
use GeoKrety\Model\Move;
use GeoKrety\Traits\GeokretLoader;

class Statistics extends BaseJson {
    use GeokretLoader;

    public function altitude_profile(\Base $f3) {
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
}
