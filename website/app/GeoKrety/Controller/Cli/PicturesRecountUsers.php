<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Picture;
use GeoKrety\Model\User;
use GeoKrety\PictureType;

class PicturesRecountUsers extends BaseCleaner {
    private $pictureModel;
    private $status;
    private $currentUserId;

    public function __construct() {
        parent::__construct();
        $this->pictureModel = new Picture();
    }

    protected function getModel(): \GeoKrety\Model\Base {
        return new User();
    }

    protected function getModelName(): string {
        return 'Users';
    }

    protected function getParamId(\Base $f3): int {
        return $f3->get('PARAMS.userid');
    }

    protected function getScriptName(): string {
        return 'pictures_recount_users';
    }

    protected function filterHook() {
        return [];
    }

    protected function orderHook() {
        return ['order' => 'joined_on_datetime ASC'];
    }

    protected function process(&$object): void {
        $picturesCount = $this->pictureModel->count(['user = ? AND type = ? AND uploaded_on_datetime != ?', $object->id, PictureType::PICTURE_USER_AVATAR, null]);
        $picturesCountOld = $this->pictureModel->pictures_count;
        $this->currentUserId = $object->id;
        $object->pictures_count = $picturesCount;
        $object->save();

        $changed = $picturesCountOld === $picturesCount;
        $this->status = ($changed ? '👍' : '👌');

        $this->processResult($object->id, $changed);
        $this->print();
    }

    protected function print(): void {
        $this->consoleWriter->print([$this->currentUserId, $this->percentProcessed, $this->counter, $this->total, $this->status]);
    }

    protected function getConsoleWriterPattern() {
        return 'Re-counting Users pictures: %s %6.2f%% (%d/%d) %s';
    }
}
