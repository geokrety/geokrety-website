<?php

namespace GeoKrety;

use DB\SQL;

class Session extends SQL\Session {
    // SQL schema update is in db/migration/session_persist

    public function cleanup($max) {
        $this->erase(['stamp + ? < ? AND persistent = FALSE OR stamp + ? < ? AND persistent = TRUE', $max, time(), GK_SITE_SESSION_LIFETIME_REMEMBER, time()]);

        return true;
    }

    public static function setPersistent($id) {
        $db = \Base::instance()->get('DB');
        $db->exec('UPDATE sessions SET persistent = TRUE WHERE session_id = ?', [$id]);
    }
}
