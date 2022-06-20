<?php

namespace GeoKrety\Controller\Cli\Traits;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Service\Xml\Base;

trait XMLDumper {
    use Script;

    public int $maxmem = 0;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->initScript();
        $this->console_writer->setPattern('%7s %6d/%d mem:%10d');
        $this->console_writer->setInteractive(true);
    }

    public function get(\Base $f3) {
        $this->script_start($this->getScriptName());
        parent::get($f3);
        $this->script_end();
    }

    protected function getCompressionMethod(): string {
        return Base::COMPRESSION_NONE; // TODO change
    }

    public function addOneOfRequiredFilter($filters) {
    }

    protected function checkRequiredFilter() {
    }

    protected function filtersHook() {
        // TODO filter by @param `period`
    }

    protected function getMem($current) {
        $mem = memory_get_peak_usage() - 68078656;
        $maxmem = max($this->maxmem, $mem);
        if ($maxmem != $this->maxmem && $current > 1) {
            fwrite(STDERR, "\n");
        }
        $this->maxmem = $maxmem;

        return $this->maxmem;
    }

    protected function processPreHookAddGeokret(Geokret &$geokret, ?int $current = null, ?int $count = null, ?int $subset_position = null, ?int $subset_total = null) {
        $this->console_writer->print([$geokret->gkid, $current + $subset_total * $subset_position, $subset_total * $count, $this->getMem($subset_position)], false, true);
    }

    protected function processPreHookAddMove(Move &$move, ?int $current = null, ?int $count = null, ?int $subset_position = null, ?int $subset_total = null) {
        $this->console_writer->print([$move->id, $current + $subset_total * $subset_position, $subset_total * $count, $this->getMem($subset_position)], false, true);
    }

    abstract protected function getScriptName(): string;
}
