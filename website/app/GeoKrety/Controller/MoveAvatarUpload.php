<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use GeoKrety\PictureType;

class MoveAvatarUpload extends AbstractPictureUpload {
    use \MoveLoader;

    protected function generateKey(): string {
        return uniqid(sprintf('MOV%06d_', $this->move->id), true);
    }

    public function getBucket(): string {
        return GK_BUCKET_NAME_MOVES_PICTURES;
    }

    public function getPictureType(): int {
        return PictureType::PICTURE_GEOKRET_MOVE;
    }

    public function getEventNameBase(): string {
        return 'move.picture';
    }

    public function setRelationships(Picture $picture): void {
        $picture->move = $this->move;
    }
}
