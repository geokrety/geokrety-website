<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use GeoKrety\PictureType;

class MoveAvatarUpload extends AbstractPictureUpload {
    use \MoveLoader;

    protected function _generateKey(): string {
        return self::generateKey($this->move->id);
    }

    public static function generateKey($id): string {
        return uniqid(sprintf('MOV%06d_', $id));
    }

    public static function getBucket(): string {
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

    /**
     * Check if the current user has permission on this object.
     *
     * @throws UploadPermissionException
     */
    protected function check_permission(): void {
        if ($this->hasWritePermission($this->move)) {
            return;
        }
        throw new UploadPermissionException(_('You have no write permission on this move'));
    }
}
