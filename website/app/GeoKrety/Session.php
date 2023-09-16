<?php

namespace GeoKrety;

use DB\SQL;
use GeoKrety\Model\User;

class Session extends SQL\Session {
    // SQL schema update is in db/migration/session_persist

    /*
     * Prevent reading a session from database if it's 'deleted'
     */
    public function read($id) {
        if ($id === 'deleted') {
            return '';
        }

        return parent::read($id);
    }

    /*
     * Prevent writing a session to database if it's 'deleted'
     */
    public function write($id, $data) {
        if ($id === 'deleted' || empty($this->_agent)) {
            return true;
        }

        return parent::write($id, $data);
    }

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
            self::configure_jar_for_gkt($f3);
            $f3->set('JAR.expires', $result[0]['stamp']);
            $f3->set('COOKIE.gkt_on_behalf', $result[0]['on_behalf']);
        }
    }

    public static function configure_jar_for_gkt(\Base $f3) {
        $f3->set('JAR.path', $f3->alias('gkt_v3_inventory'));
        $f3->set('JAR.samesite', 'None');
        $f3->set('JAR.secure', $f3->get('SCHEME') === 'https');
        $f3->set('JAR.httponly', false);
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

    public static function closeCurrentSession() {
        $sessid = session_id();
        if ($sessid === false) {
            return;
        }
        $f3 = \Base::instance();
        $f3->get('DB')->exec('DELETE FROM sessions WHERE "session_id" = ?', [$sessid]);
    }
}
