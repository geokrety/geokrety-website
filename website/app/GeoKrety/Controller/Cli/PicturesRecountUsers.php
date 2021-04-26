<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Base;
use GeoKrety\Model\Picture;
use GeoKrety\Model\User;
use GeoKrety\PictureType;

class PicturesRecountUsers extends BaseCleaner {
    private Picture $pictureModel;
    private string $status;
    private int $currentUserId;
    protected string $class_name = __CLASS__;

    public function __construct() {
        parent::__construct();
        $this->pictureModel = new Picture();
    }

    protected function getModel(): Base {
        return new User();
    }

    protected function getModelName(): string {
        return 'Users';
    }

    protected function getParamId(\Base $f3): int {
        return $f3->get('PARAMS.userid');
    }

    protected function filterHook(): array {
        return [];
    }

    protected function orderHook(): array {
        return ['order' => 'joined_on_datetime ASC'];
    }

    protected function process($object): void {
        $picturesCount = $this->pictureModel->count(['user = ? AND type = ? AND uploaded_on_datetime != ?', $object->id, PictureType::PICTURE_USER_AVATAR, null]);
        $picturesCountOld = $this->pictureModel->pictures_count;
        $this->currentUserId = $object->id;
        $object->pictures_count = $picturesCount;
        $object->save();

        $changed = $picturesCountOld === $picturesCount;
        $this->status = ($changed ? '👍' : '👌');

        $this->processResult($changed);
        $this->print();
    }

    protected function print(): void {
        $this->console_writer->print([$this->currentUserId, $this->percentProcessed, $this->counter, $this->total, $this->status]);
    }

    protected function getConsoleWriterPattern(): string {
        return 'Re-counting Users pictures: %s %6.2f%% (%d/%d) %s';
    }
}
