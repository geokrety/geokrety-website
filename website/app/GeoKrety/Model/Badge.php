<?php

namespace GeoKrety\Model;

class Badge extends Base {
    protected $db = 'DB';
    protected $table = 'gk-badges';

    protected $fieldConf = array(
        'holder' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
    );

    public function get_awarded_on_datetime($value) {
        return self::get_date_object($value);
    }
}
