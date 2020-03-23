<?php

namespace GeoKrety\Controller;

use GeoKrety\PictureType;
use GeoKrety\Service\S3Client;
use GeoKrety\Service\Smarty;

class PictureDelete extends Base {
    use \PictureLoader;

    public function get() {
        Smarty::render('extends:full_screen_modal.tpl|dialog/picture_delete.tpl');
    }

    public function get_ajax() {
        Smarty::render('extends:base_modal.tpl|dialog/picture_delete.tpl');
    }

    public function delete(\Base $f3) {
        $s3 = S3Client::instance()->getS3Public();
        // TODO: Not sure how to validate the deletion in the backend
        $s3->deleteObject([
            'Bucket' => $this->picture->bucket,
            'Key' => $this->picture->key,
        ]);
        $s3->deleteObject([
            'Bucket' => S3Client::getThumbnailBucketName($this->picture->bucket),
            'Key' => $this->picture->key,
        ]);

        $this->picture->erase();
        \Event::instance()->emit('picture.deleted', $this->picture);

        if ($this->picture->isType(PictureType::PICTURE_GEOKRET_AVATAR)) {
            $f3->reroute(['geokret_details', ['gkid' => $this->picture->geokret->gkid], '#gk-avatars-list']);
        }
        if ($this->picture->isType(PictureType::PICTURE_USER_AVATAR)) {
            $f3->reroute(['user_details', ['userid' => $this->picture->user->id]], '#user-avatars-list');
        }
        if ($this->picture->isType(PictureType::PICTURE_GEOKRET_MOVE)) {
            $f3->reroute(['geokret_details', ['gkid' => $this->picture->move->geokret->gkid], '#log'.$this->picture->move->id]);
        }
    }
}
