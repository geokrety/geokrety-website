<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Email\DailyDigest as EmailDailyDigest;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\GeokretNearHome;
use GeoKrety\Model\Move;
use GeoKrety\Model\MoveComment;
use GeoKrety\Model\News;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\StaticMapImage;
use GeoKrety\Service\UserSettings;
use PHPMailer\PHPMailer\Exception;
use Sugar\Event;

class DailyDigest {
    use Script;

    private ?\DateTime $since;
    private EmailDailyDigest $email;
    private User $user;
    private int $updates_count = 0;

    public function __construct() {
        $this->initScript();
        $this->console_writer->setPattern('%5d %20s => %14s');
    }

    public function sendDaily() {
        $this->script_start(__METHOD__);
        $sql = <<<'SQL'
WITH gks AS (
    SELECT distinct(geokret) AS gkid
    FROM gk_moves
    WHERE moved_on_datetime >= ?
)

SELECT distinct(owner) AS userid
FROM gk_geokrety
WHERE id IN (SELECT gkid FROM gks)
AND owner IS NOT NULL

UNION DISTINCT

SELECT distinct("user") AS userid
FROM gk_watched
WHERE geokret IN (SELECT gkid FROM gks)

UNION DISTINCT

SELECT distinct(author) AS userid
FROM gk_moves_comments AS mc
WHERE mc."move" IN (
    SELECT distinct("move")
    FROM gk_moves_comments AS mc2
    WHERE mc2.created_on_datetime >= ?
)

UNION DISTINCT

SELECT distinct(author) AS userid
FROM gk_moves AS mv
WHERE mv.id IN (
    SELECT distinct("move")
    FROM gk_moves_comments AS mc
    WHERE mc.created_on_datetime >= ?
)
AND author IS NOT NULL

UNION DISTINCT

SELECT distinct(owner) AS userid
FROM gk_geokrety AS gk
WHERE gk.id IN (
    SELECT distinct(geokret)
    FROM gk_moves_comments AS mc
    WHERE mc.created_on_datetime >= ?
)
AND owner IS NOT NULL

UNION DISTINCT

SELECT distinct(c_user_id) AS userid
FROM gk_geokrety_near_users_homes
WHERE moved_on_datetime >= ?
AND missing = ?
;
SQL;

        $since = new \DateTime();
        $since->sub(new \DateInterval('P1D'))->setTime(0, 0);
        $params = array_fill(0, 5, $since->format(GK_DB_DATETIME_FORMAT));
        $params[] = Geokret::GEOKRETY_PRESENT_IN_CACHE;
        $this->console_writer->print(['sql', 'finding users', 'prepare'], true);
        $result = \Base::instance()->get('DB')->exec($sql, $params);
        $this->user = new User();
        foreach ($result as $_userid) {
            $this->user->load(['id = ?', $_userid['userid']]);
            $this->console_writer->print([$this->user->id, $this->user->username, 'prepare']);
            $this->send();
        }
        $this->script_end();
    }

    public function sendUser(\Base $f3) {
        $this->script_start(__METHOD__);
        $userid = $f3->get('PARAMS.userid');
        $this->console_writer->print([$userid, '', 'prepare']);
        $this->user = new User();
        $this->user->load(['id = ?', $userid]);
        if ($this->user->dry()) {
            $this->console_writer->print([$this->user->id, $this->user->username, '404 skipped'], true);

            return;
        }
        $this->send();
        $this->script_end();
    }

    protected function send() {
        if (!$this->user->hasEmail()) {
            $this->console_writer->print([$this->user->id, $this->user->username, '406 no email'], true);
            Event::instance()->emit('cron.dailydigest.nomail', $this->user);

            return;
        }

        $userSettings = UserSettings::instance();
        $daily_digest_enabled = $userSettings->get($this->user, 'DAILY_DIGEST');

        if (!$daily_digest_enabled) {
            $this->console_writer->print([$this->user->id, $this->user->username, '403 don\'t want'], true);
            Event::instance()->emit('cron.dailydigest.deny', $this->user);

            return;
        }

        if (!$this->user->isEmailValid()) {
            $this->console_writer->print([$this->user->id, $this->user->username, sprintf('412 invalid email status (%d)', $this->user->email_invalid)], true);
            Event::instance()->emit('cron.dailydigest.invalid-mail', $this->user);

            return;
        }

        $this->email = new EmailDailyDigest();

        try {
            $this->compute_since();
            $this->load_news();
            $this->load_last_moves_owned_geokrety();
            $this->load_last_moves_watched_geokrety();
            $this->load_moves_comments();
            $this->load_near_home_dropped_geokrety();

            if (!$this->updates_count) {
                $this->console_writer->print([$this->user->id, $this->user->username, '204 empty'], true);
                Event::instance()->emit('cron.dailydigest.empty', $this->user);

                return;
            }

            $this->updates_count = 0;
            $this->console_writer->print([$this->user->id, $this->user->username, 'sending']);
            $this->email->sendDailyDigest($this->user);
        } catch (Exception $e) {
            $this->console_writer->print([$this->user->id, $this->user->username, sprintf('500 error: %s', $e->getMessage())], true);
            Event::instance()->emit('cron.dailydigest.error', $this->email);

            return;
        }
        $this->console_writer->print([$this->user->id, $this->user->username, '200 sent'], true);
        Event::instance()->emit('cron.dailydigest.sent', $this->email);
    }

    private function compute_since() {
        $this->since = $this->user->last_mail_datetime;
        if (is_null($this->since) or $this->since->diff(new \DateTime('now'))->format('%a') > 1) {
            $this->since = new \DateTime();
            $this->since->sub(new \DateInterval('P1D'));
        }
        $this->email->setSince($this->since);
    }

    private function load_news() {
        $this->console_writer->print([$this->user->id, $this->user->username, 'load news']);
        $news = new News();
        $sNews = $news->find(['NOW() - created_on_datetime < cast(? as interval)', GK_SITE_NEWS_EMAIL_DAYS_VALIDITY.' DAY'], ['order' => 'created_on_datetime DESC']);
        Smarty::assign('news', $sNews);
    }

    private function load_last_moves_owned_geokrety() {
        $this->console_writer->print([$this->user->id, $this->user->username, 'load owned']);
        $move = new Move();
        $move->has('geokret', ['owner = ?', $this->user->id]);
        $moves = $move->find(['moved_on_datetime >= ?', $this->since->format(GK_DB_DATETIME_FORMAT)], ['order' => 'moved_on_datetime DESC', 'limit' => 200]);
        $this->updates_count += $moves ? 1 : 0;
        Smarty::assign('moves', $moves);
    }

    private function load_last_moves_watched_geokrety() {
        $this->console_writer->print([$this->user->id, $this->user->username, 'load watched']);
        $move = new Move();
        $move->has('geokret.watchers', ['user = ?', $this->user->id]);
        $watched = $move->find(['moved_on_datetime >= ?', $this->since->format(GK_DB_DATETIME_FORMAT)], ['order' => 'moved_on_datetime DESC', 'limit' => 200]);
        $this->updates_count += $watched ? 1 : 0;
        Smarty::assign('watched', $watched);
    }

    private function load_moves_comments() {
        $this->console_writer->print([$this->user->id, $this->user->username, 'load moves_comments']);
        $sql = <<<'SQL'
SELECT mc.*
FROM gk_moves_comments AS mc
LEFT JOIN gk_geokrety AS gk ON mc.geokret = gk.id
LEFT JOIN gk_moves AS mv ON mc.move = mv.id
WHERE mc.created_on_datetime >= ?
AND (
       gk.owner = ?
    OR mv.author = ?
    OR mc.id IN (
        SELECT id
        FROM gk_moves_comments AS mcr
        WHERE mcr.author = ?
    )
)
ORDER BY mc.created_on_datetime DESC
LIMIT 100
SQL;
        $comment = new MoveComment();
        $comments = $comment->findByRawSQL($sql, [$this->since->format(GK_DB_DATETIME_FORMAT), $this->user->id, $this->user->id, $this->user->id]);
        $this->updates_count += sizeof($comments) ? 1 : 0;
        Smarty::assign('comments', sizeof($comments) ? $comments : false);
    }

    /**
     * @throws Exception
     */
    private function load_near_home_dropped_geokrety() {
        $this->console_writer->print([$this->user->id, $this->user->username, 'load dropped']);
        Smarty::assign('gk_near_home', false);
        if (!$this->user->hasHomeCoordinates()) {
            return;
        }

        $gkNearHome = new GeokretNearHome();
        $gk_near_home = $gkNearHome->find(['c_user_id = ? AND moved_on_datetime >= ?', $this->user->id, $this->since->format(GK_DB_DATETIME_FORMAT)]);
        $this->updates_count += $gk_near_home ? 1 : 0;
        Smarty::assign('gk_near_home', $gk_near_home);
        if (!$gk_near_home) {
            return;
        }

        $sql = <<<SQL
SELECT json_build_object(
    'type', 'FeatureCollection',
    'features', COALESCE(json_agg(public.ST_AsGeoJSON(t.*)::json), '[]')::jsonb
) AS geojson
FROM (
    SELECT position
    FROM gk_geokrety_near_users_homes
    WHERE c_user_id = ?
    AND moved_on_datetime >= ?
    AND missing = ?
    LIMIT 500
) as t;
SQL;
        $this->console_writer->print([$this->user->id, $this->user->username, 'load dropped - geojson']);
        $result = \Base::instance()->get('DB')->exec($sql, [$this->user->id, $this->since->format(GK_DB_DATETIME_FORMAT), Geokret::GEOKRETY_PRESENT_IN_CACHE]);
        $positions = $result;

        $this->console_writer->print([$this->user->id, $this->user->username, 'load dropped - image']);
        $imgCid = 'GK_NEAR_HOME_IMG';
        if (StaticMapImage::generateHomeMapWithMarkers($this->email, $this->user, $positions, $imgCid)) {
            Smarty::assign('gk_near_home_img', $imgCid);
        }
    }
}
