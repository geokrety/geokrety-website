<?php

namespace GeoKrety;

use DB\SQL;
use GeoKrety\Model\User;

class Session extends SQL\Session {
    public const BOT_REGEX = '/BotLink|bingbot|AhrefsBot|ahoy|AlkalineBOT|anthill|appie|arale|araneo|AraybOt|ariadne|arks|ATN_Worldwide|Atomz|bbot|Bjaaland|Ukonline|borg\-bot\/0\.9|boxseabot|bspider|calif|christcrawler|CMC\/0\.01|combine|confuzzledbot|CoolBot|cosmos|Internet Cruiser Robot|cusco|cyberspyder|cydralspider|desertrealm, desert realm|digger|DIIbot|grabber|downloadexpress|DragonBot|dwcp|ecollector|ebiness|elfinbot|esculapio|esther|fastcrawler|FDSE|FELIX IDE|ESI|fido|H�m�h�kki|KIT\-Fireball|fouineur|Freecrawl|gammaSpider|gazz|gcreep|golem|googlebot|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|iajabot|INGRID\/0\.1|Informant|InfoSpiders|inspectorwww|irobot|Iron33|JBot|jcrawler|Teoma|Jeeves|jobo|image\.kapsi\.net|KDD\-Explorer|ko_yappo_robot|label\-grabber|larbin|legs|Linkidator|linkwalker|Lockon|logo_gif_crawler|marvin|mattie|mediafox|MerzScope|NEC\-MeshExplorer|MindCrawler|udmsearch|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|sharp\-info\-agent|WebMechanic|NetScoop|newscan\-online|ObjectsSearch|Occam|Orbsearch\/1\.0|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|Getterrobo\-Plus|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Search\-AU|searchprocess|Senrigan|Shagseeker|sift|SimBot|Site Valet|skymob|SLCrawler\/2\.0|slurp|ESI|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|nil|suke|http:\/\/www\.sygol\.com|tach_bw|TechBOT|templeton|titin|topiclink|UdmSearch|urlck|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|crawlpaper|wapspider|WebBandit\/1\.0|webcatcher|T\-H\-U\-N\-D\-E\-R\-S\-T\-O\-N\-E|WebMoose|webquest|webreaper|webs|webspider|WebWalker|wget|winona|whowhere|wlm|WOLP|WWWC|none|XGET|Nederland\.zoek|AISearchBot|woriobot|NetSeer|Nutch|YandexBot|YandexMobileBot|SemrushBot|FatBot|MJ12bot|DotBot|AddThis|baiduspider|m2e/i';

    /*
     * Prevent reading a session from database if it's 'deleted'
     */
    public function read($id) {
        if ($id === 'deleted') {
            return '';
        }
        $this->load(['session_id=? AND stamp >= ?', $this->sid = $id, time()]);
        if ($this->dry()) {
            $f3 = \Base::instance();
            $f3->get('DB')->exec('DELETE FROM sessions WHERE session_id=? AND stamp < ?', [$this->sid, time()]);

            return '';
        }
        if ($this->get('ip') != $this->_ip || $this->get('agent') != $this->_agent) {
            $fw = \Base::instance();
            if (!isset($this->onsuspect)
                || $fw->call($this->onsuspect, [$this, $id]) === false) {
                // NB: `session_destroy` can't be called at that stage (`session_start` not completed)
                $this->destroy($id);
                $this->close();
                unset($fw->{'COOKIE.'.session_name()});
                $fw->error(403);
            }
        }

        return $this->get('data');
    }

    /*
     * Prevent writing a session to database if it's 'deleted'
     */
    public function write($id, $data) {
        if ($id === 'deleted' || empty($this->_agent)) {
            return true;
        }
        $session_lifetime = $this->session_remember_seconds();
        if ($session_lifetime === 0) {
            return true;
        }
        $this->set('session_id', $id);
        $this->set('data', $data);
        $this->set('ip', $this->_ip);
        $this->set('agent', $this->_agent);
        $this->set('stamp', time() + $session_lifetime);
        $this->save();

        return true;
    }

    public function isBot() {
        return !$this->_agent || preg_match(self::BOT_REGEX, $this->_agent);
    }

    protected function session_remember_seconds() {
        if ($this->persistent === true) {
            return GK_SITE_SESSION_LIFETIME_REMEMBER;
        } elseif ($this->isBot()) {
            return GK_SITE_SESSION_NON_LIVED_REMEMBER;
        } elseif (\Base::instance()->get('GET.short_lived_session_token') === GK_SITE_SESSION_SHORT_LIVED_TOKEN) {
            return GK_SITE_SESSION_NON_LIVED_REMEMBER;
        } elseif (!is_null($this->user)) {
            return GK_SITE_SESSION_REMEMBER;
        }

        // mostly scripts
        return GK_SITE_SESSION_SHORT_LIVED_REMEMBER;
    }

    public static function cleanExpired() {
        $f3 = \Base::instance();
        $f3->get('DB')->exec('DELETE FROM sessions WHERE stamp < ?', [time()]);

        return true;
    }

    public static function setPersistent() {
        $f3 = \Base::instance();
        $id = $f3->get('COOKIE.PHPSESSID');
        $f3->set('COOKIE.PHPSESSID', $f3->get('COOKIE.PHPSESSID'), GK_SITE_SESSION_LIFETIME_REMEMBER); // Overwrite session expire date
        $f3->get('DB')->exec('UPDATE sessions SET persistent = TRUE, stamp = ? WHERE session_id = ?', [time() + GK_SITE_SESSION_LIFETIME_REMEMBER, $id]);
    }

    public static function setGKTCookie() {
        $f3 = \Base::instance();
        $id = $f3->get('COOKIE.PHPSESSID');

        $result = $f3->get('DB')->exec('SELECT on_behalf, stamp FROM sessions WHERE session_id = ?', [$id]);
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
