<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\Picture;
use GeoKrety\PictureType;
use GeoKrety\Traits\GeokretLoader;

class GeokretAvatarUpload extends AbstractPictureUpload {
    use GeokretLoader;

    protected function generateKey(): string {
        return uniqid(sprintf('%s_', $this->geokret->gkid), true);
    }

    public function getBucket(): string {
        return GK_BUCKET_NAME_GEOKRETY_AVATARS;
    }

    public function getPictureType(): int {
        return PictureType::PICTURE_GEOKRET_AVATAR;
    }

    public function getEventNameBase(): string {
        return 'geokret.avatar';
    }

    public function setRelationships(Picture $picture): void {
        $f3 = \Base::instance(); //get('PARAMS.gkid');
        $picture->geokret = Geokret::gkid2id($f3->get('PARAMS.gkid'));
    }
}
