<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Base;
use GeoKrety\Model\Move;
use GeoKrety\Model\Picture;
use GeoKrety\PictureType;

class PicturesRecountMoves extends BaseCleaner {
    private Picture $pictureModel;
    private string $status;
    private int $currentMoveId;
    protected string $class_name = __CLASS__;

    public function __construct() {
        parent::__construct();
        $this->pictureModel = new Picture();
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
        return [];
    }

    protected function orderHook(): array {
        return ['order' => 'created_on_datetime ASC'];
    }

    protected function process($object): void {
        $picturesCount = $this->pictureModel->count(['move = ? AND type = ? AND uploaded_on_datetime != ?', $object->id, PictureType::PICTURE_GEOKRET_MOVE, null]);
        $picturesCountOld = $this->pictureModel->pictures_count;
        $this->currentMoveId = $object->id;
        $object->pictures_count = $picturesCount;
        $object->save();

        $changed = $picturesCountOld === $picturesCount;
        $this->status = ($changed ? '👍' : '👌');

        $this->processResult($changed);
        $this->print();
    }

    protected function print(): void {
        $this->console_writer->print([$this->currentMoveId, $this->percentProcessed, $this->counter, $this->total, $this->status]);
    }

    protected function getConsoleWriterPattern(): string {
        return 'Re-counting Moves pictures: %s %6.2f%% (%d/%d) %s';
    }
}
