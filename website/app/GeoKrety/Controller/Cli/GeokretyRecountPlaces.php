<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Base;
use GeoKrety\Model\Geokret;

class GeokretyRecountPlaces extends BaseCleaner {
    private string $status;
    private string $currentGkid;
    protected string $class_name = __CLASS__;

    public function __construct() {
        parent::__construct();
    }

    protected function getModel(): Base {
        return new Geokret();
    }

    protected function getModelName(): string {
        return 'GeoKrety';
    }

    protected function getParamId(\Base $f3): int {
        return Geokret::gkid2id($f3->get('PARAMS.gkid'));
    }

    protected function filterHook(): array {
        return [];
    }

    protected function orderHook(): array {
        return ['order' => 'created_on_datetime ASC'];
    }

    protected function process($object): void {
        $sql = <<<'SQL'
SELECT geokret_compute_total_places_visited(?),
       geokret_compute_total_distance(?)
FROM geokrety.gk_geokrety;
SQL;

        \Base::instance()->get('DB')->exec($sql, [$object->id, $object->id]);
        $this->currentGkid = $object->gkid;

        $this->processResult(true);
        $this->print();
    }

    /**
     * @throws \Exception
     */
    public function processAll(\Base $f3) {
        $this->script_start($this->class_name.'::'.__FUNCTION__);

        $sql = <<<'SQL'
SELECT geokret_compute_total_places_visited(id),
       geokret_compute_total_distance(id)
FROM geokrety.gk_geokrety;
SQL;
        \Base::instance()->get('DB')->exec($sql);

        $this->script_end();
    }

    protected function print(): void {
        $this->console_writer->print([$this->currentGkid, $this->percentProcessed, $this->counter, $this->total]);
    }

    protected function getConsoleWriterPattern(): string {
        return 'Updating GeoKrety total places visited and distance: %s %6.2f%% (%d/%d)';
    }
}
