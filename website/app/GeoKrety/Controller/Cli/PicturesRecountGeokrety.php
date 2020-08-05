<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\Picture;
use GeoKrety\PictureType;

class PicturesRecountGeokrety extends BaseCleaner {
    private $pictureModel;
    private $status;
    private $currentGkid;

    public function __construct() {
        parent::__construct();
        $this->pictureModel = new Picture();
    }

    protected function getModel(): \GeoKrety\Model\Base {
        return new Geokret();
    }

    protected function getModelName(): string {
        return 'GeoKrety';
    }

    protected function getParamId(\Base $f3): int {
        return Geokret::gkid2id($f3->get('PARAMS.gkid'));
    }

    protected function getScriptName(): string {
        return 'pictures_recount_geokrety';
    }

    protected function filterHook() {
        return [];
    }

    protected function orderHook() {
        return ['order' => 'created_on_datetime ASC'];
    }

    protected function process(&$object): void {
        $picturesCount = $this->pictureModel->count(['geokret = ? AND type = ? AND uploaded_on_datetime != ?', $object->id, PictureType::PICTURE_GEOKRET_AVATAR, null]);
        $picturesCountOld = $this->pictureModel->pictures_count;
        $this->currentGkid = $object->gkid;
        $object->pictures_count = $picturesCount;
        $object->save();

        $changed = $picturesCountOld === $picturesCount;
        $this->status = ($changed ? '👍' : '👌');

        $this->processResult($object->id, $changed);
        $this->print();
    }

    protected function print(): void {
        $this->consoleWriter->print([$this->currentGkid, $this->percentProcessed, $this->counter, $this->total, $this->status]);
    }

    protected function getConsoleWriterPattern() {
        return 'Re-counting GeoKrety pictures: %s %6.2f%% (%d/%d) %s';
    }
}
