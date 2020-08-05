<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Move;
use GeoKrety\Model\Picture;
use GeoKrety\PictureType;

class PicturesRecountMoves extends BaseCleaner {
    private $pictureModel;
    private $status;
    private $currentMoveId;

    public function __construct() {
        parent::__construct();
        $this->pictureModel = new Picture();
    }

    protected function getModel(): \GeoKrety\Model\Base {
        return new Move();
    }

    protected function getModelName(): string {
        return 'Moves';
    }

    protected function getParamId(\Base $f3): int {
        return $f3->get('PARAMS.moveid');
    }

    protected function getScriptName(): string {
        return 'pictures_recount_moves';
    }

    protected function filterHook() {
        return [];
    }

    protected function orderHook() {
        return ['order' => 'created_on_datetime ASC'];
    }

    protected function process(&$object): void {
        $picturesCount = $this->pictureModel->count(['move = ? AND type = ? AND uploaded_on_datetime != ?', $object->id, PictureType::PICTURE_GEOKRET_MOVE, null]);
        $picturesCountOld = $this->pictureModel->pictures_count;
        $this->currentMoveId = $object->id;
        $object->pictures_count = $picturesCount;
        $object->save();

        $changed = $picturesCountOld === $picturesCount;
        $this->status = ($changed ? '👍' : '👌');

        $this->processResult($object->id, $changed);
        $this->print();
    }

    protected function print(): void {
        $this->consoleWriter->print([$this->currentMoveId, $this->percentProcessed, $this->counter, $this->total, $this->status]);
    }

    protected function getConsoleWriterPattern() {
        return 'Re-counting Moves pictures: %s %6.2f%% (%d/%d) %s';
    }
}
