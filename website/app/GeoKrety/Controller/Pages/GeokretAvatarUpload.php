<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use GeoKrety\PictureType;
use GeoKrety\Traits\GeokretLoader;

class GeokretAvatarUpload extends AbstractPictureUpload {
    use GeokretLoader;

    protected function _generateKey(): string {
        return self::generateKey($this->geokret->gkid);
    }

    public static function generateKey($id): string {
        return uniqid(sprintf('%s_', $id));
    }

    public static function getBucket(): string {
        return GK_BUCKET_NAME_GEOKRETY_AVATARS;
    }

    public function getPictureType(): int {
        return PictureType::PICTURE_GEOKRET_AVATAR;
    }

    public function getEventNameBase(): string {
        return 'geokret.avatar';
    }

    public function setRelationships(Picture $picture): void {
        $picture->geokret = $this->geokret->id;
    }

    /**
     * Check if the current user has permission on this object.
     *
     * @throws UploadPermissionException
     */
    protected function check_permission(\Base $f3): void {
        if ($this->geokret->isOwner()) {
            return;
        }
        throw new UploadPermissionException(_('You are not the GeoKret owner'));
    }
}
