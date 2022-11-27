<?php

namespace GeoKrety\Service;

use Caxy\BaseX\Session;

class BaseXClient extends \Prefab {
    private \Caxy\BaseX\Session $basex_session;

    public function getSession(): Session {
        return $this->basex_session;
    }

    public function __construct() {
        $this->basex_session = new Session(
            GK_BASEX_HOST,
            GK_BASEX_PORT,
            GK_BASEX_USER,
            GK_BASEX_PASSWORD,
        );
    }
}
