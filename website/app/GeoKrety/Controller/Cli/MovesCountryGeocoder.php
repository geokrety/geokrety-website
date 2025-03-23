<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Base;
use GeoKrety\Model\Move;

class MovesCountryGeocoder extends BaseCleaner {
    private Move $moveModel;
    private int $currentMoveId;
    protected string $class_name = __CLASS__;

    public function __construct() {
        parent::__construct();
        $this->moveModel = new Move();
    }

    protected function getModel(): Base {
        return new Move();
    }

    protected function getModelName(): string {
        return 'Moves';
    }

    protected function getParamId(\Base $f3): int {
        return $f3->get('PARAMS.moveid');
    }

    protected function filterHook(): array {
        return ['position != ?', null];
    }

    protected function orderHook(): array {
        return ['order' => 'id ASC'];
    }

    protected function process($object): void {
        $sql = <<< EOF
UPDATE geokrety.gk_moves
SET country=(
    SELECT iso_a2 FROM public.countries
    WHERE public.ST_Intersects(geom::public.geometry, geokrety.gk_moves.position::public.geometry)
)
WHERE id = ?
AND geokrety.gk_moves.position IS NOT NULL;
EOF;

        $this->db->exec($sql, [$object->id]);
        $this->currentMoveId = $object->id;

        $this->processResult(true);
        $this->print();
    }

    protected function print(): void {
        $this->console_writer->print([$this->currentMoveId, $this->percentProcessed, $this->counter, $this->total]);
    }

    protected function getConsoleWriterPattern(): string {
        return 'Geocoding Moves country: %s %6.2f%% (%d/%d)';
    }
}
