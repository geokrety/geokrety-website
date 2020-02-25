<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\S3Client;
use GeoKrety\Service\Smarty;

class GeokretAvatarDelete extends Base {
    use \PictureLoader;

    public function get() {
        Smarty::render('extends:full_screen_modal.tpl|dialog/geokret_avatar_delete.tpl');
    }

    public function get_ajax() {
        Smarty::render('extends:base_modal.tpl|dialog/geokret_avatar_delete.tpl');
    }

    public function delete(\Base $f3) {
        $s3 = S3Client::instance()->getS3Public();
        // TODO: Not sure how to validate the deletion in the backend
        $s3->deleteObject([
            'Bucket' => GK_BUCKET_NAME_GEOKRETY_AVATARS,
            'Key' => $this->picture->key,
        ]);
        $s3->deleteObject([
            'Bucket' => S3Client::getThumbnailBucketName(GK_BUCKET_NAME_GEOKRETY_AVATARS),
            'Key' => $this->picture->key,
        ]);

        $this->picture->erase();
        \Event::instance()->emit('geokret.avatar.image.deleted', $this->picture);
        $f3->reroute(['geokret_details', ['gkid' => $this->picture->geokret->gkid]]);
    }
}
