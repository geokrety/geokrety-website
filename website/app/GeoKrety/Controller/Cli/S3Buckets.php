<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Model\Picture as PictureModel;

class S3Buckets {
    use Script;

    public function prune(\Base $f3): void {
        $this->script_start(__METHOD__);
        PictureModel::expireNeverUploaded();
        $this->script_end();
    }
}
