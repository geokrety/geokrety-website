<?php

namespace GeoKrety\Model;

class Watched extends Base {
    protected $db = 'DB';
    protected $table = 'gk-watched';

    protected $fieldConf = array(
        'user' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
        ),
        'geokret' => array(
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
        ),
    );

    public function isWatcher() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->user) && $f3->get('SESSION.CURRENT_USER') === $this->user->id;
    }
}
