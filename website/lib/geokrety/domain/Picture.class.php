<?php

namespace Geokrety\Domain;

class Picture {
    public $id;
    public $type;
    public $geokretId;
    public $userId;
    public $filename;
    public $ownerName='xxx'; // TODO update ldjson
    public $legend;

    public function isAvatar() {
      // TODO
    }
}
