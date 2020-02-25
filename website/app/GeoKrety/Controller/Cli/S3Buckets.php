<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Picture as PictureModel;

class S3Buckets {
    public function pruneGeokretyAvatars(\Base $f3): void {
        if ($f3->exists('LOCK.S3Buckets.pruneGeokretyAvatars')) {
            echo "\e[0;31mAnother task is already running\e[0m".PHP_EOL;
        }

        echo "\e[0;32mLaunch task\e[0m".PHP_EOL;
        $f3->set('LOCK.S3Buckets.pruneGeokretyAvatars', 'true', 60);
        // TODO expire never uploaded pictures
        PictureModel::expireNeverUploaded();
        $f3->clear('LOCK.S3Buckets.pruneGeokretyAvatars');
        echo "\e[0;32mTask end\e[0m".PHP_EOL;
    }
}
