<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\Picture;
use GeoKrety\PictureType;

class GeokretAvatarUpload extends AbstractPictureUpload {

    protected function generateKey(): string {
        return uniqid(sprintf('%s_', $this->geokret->gkid), true);
    }

    public function getBucket(): string {
        return GK_BUCKET_NAME_GEOKRETY_AVATARS;
    }

    public function getEventNameBase(): string {
        return 'geokret.avatar';
    }

    public function generatePictureObject(\base $f3): Picture {
        $picture = new Picture();
        $picture->bucket = GK_BUCKET_NAME_GEOKRETY_AVATARS;
        $picture->key = $this->getImgKey();
        $picture->type = PictureType::PICTURE_GEOKRET_AVATAR;
        $picture->geokret = Geokret::gkid2id($f3->get('PARAMS.gkid'));
        if ($f3->exists('POST.filename')) {
            $picture->filename = $f3->get('POST.filename');
        }
        $picture->caption = null;

        return $picture;
    }

}
