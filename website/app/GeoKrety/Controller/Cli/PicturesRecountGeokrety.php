<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Base;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Picture;
use GeoKrety\PictureType;

class PicturesRecountGeokrety extends BaseCleaner {
    private Picture $pictureModel;
    private string $status;
    private string $currentGkid;
    protected string $class_name = __CLASS__;

    public function __construct() {
        parent::__construct();
        $this->pictureModel = new Picture();
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
        $picturesCount = $this->pictureModel->count(['geokret = ? AND type = ? AND uploaded_on_datetime != ?', $object->id, PictureType::PICTURE_GEOKRET_AVATAR, null]);
        $picturesCountOld = $this->pictureModel->pictures_count;
        $this->currentGkid = $object->gkid;
        $object->pictures_count = $picturesCount;
        $object->save();

        $changed = $picturesCountOld === $picturesCount;
        $this->status = ($changed ? '👍' : '👌');

        $this->processResult($changed);
        $this->print();
    }

    protected function print(): void {
        $this->console_writer->print([$this->currentGkid, $this->percentProcessed, $this->counter, $this->total, $this->status]);
    }

    protected function getConsoleWriterPattern(): string {
        return 'Re-counting GeoKrety pictures: %s %6.2f%% (%d/%d) %s';
    }
}
