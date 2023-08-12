<?php

namespace GeoKrety\Model;

class SiteStats extends Base {
    protected $db = 'DB';
    protected $table = 'gk_statistics_counters';

    public function jsonSerialize(): mixed {
        return [];
    }
}
