<?php

namespace GeoKrety\Model;

use GeoKrety\Service\HTMLPurifier;

class MoveComment extends Base {
    protected $db = 'DB';
    protected $table = 'gk-ruchy-comments';

    protected $fieldConf = array(
        'author' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'geokret' => array(
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ),
        'move' => array(
            'belongs-to-one' => '\GeoKrety\Model\Move',
        ),
    );

    public function set_content($value) {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isAuthor() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }
}
