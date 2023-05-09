<?php

namespace GeoKrety;

use DB\SQL;
use GeoKrety\Model\User;

class Session extends SQL\Session {
    // SQL schema update is in db/migration/session_persist

    public function cleanup($max) {
        $this->erase(['stamp + ? < ? AND persistent = FALSE OR stamp + ? < ? AND persistent = TRUE', $max, time(), GK_SITE_SESSION_LIFETIME_REMEMBER, time()]);

        return true;
    }

    public static function setPersistent() {
        $f3 = \Base::instance();
        $id = $f3->get('COOKIE.PHPSESSID');
        $f3->set('COOKIE.PHPSESSID', $f3->get('COOKIE.PHPSESSID'), GK_SITE_SESSION_LIFETIME_REMEMBER); // Overwrite session expire date
        $f3->get('DB')->exec('UPDATE sessions SET persistent = TRUE WHERE session_id = ?', [$id]);
    }

    public static function setGKTCookie() {
        $f3 = \Base::instance();
        $id = $f3->get('COOKIE.PHPSESSID');

        $result = $f3->get('DB')->exec('SELECT on_behalf FROM sessions WHERE session_id = ?', [$id]);
        if (sizeof($result) > 0) {
            $f3->set('JAR.path', $f3->alias('gkt_v3_inventory'));
            $f3->set('JAR.samesite', 'None');
            $f3->set('JAR.secure', true);
            $f3->set('JAR.httponly', false);
            $f3->set('COOKIE.gkt_on_behalf', $result[0]['on_behalf']);
        }
    }

    public static function setUserId(User $user) {
        $f3 = \Base::instance();
        $id = $f3->get('COOKIE.PHPSESSID');
        $f3->get('DB')->exec('UPDATE sessions SET "user" = ? WHERE session_id = ?', [$user->id, $id]);
    }

    public static function closeAllSessionsForUser(User $user) {
        $f3 = \Base::instance();
        $f3->get('DB')->exec('DELETE FROM sessions WHERE "user" = ?', [$user->id]);
    }
}
