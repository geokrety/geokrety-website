<?php

namespace GeoKrety\Controller\Cli;

use Base;
use DateInterval;
use DateTime;
use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Email\DailyMail as EmailDailyMail;
use GeoKrety\Model\GeokretNearHome;
use GeoKrety\Model\Move;
use GeoKrety\Model\MoveComment;
use GeoKrety\Model\News;
use GeoKrety\Model\User;
use GeoKrety\Service\File;
use GeoKrety\Service\Smarty;
use PHPMailer\PHPMailer\Exception;
use Sugar\Event;

class DailyMail {
    use Script;

    private ?DateTime $since;
    private EmailDailyMail $email;
    private User $user;
    private int $updates_count = 0;

    public function __construct() {
        $this->initScript();
        $this->console_writer->setPattern('%5d %20s => %14s');
    }

    public function sendDaily() {
        $this->start(__METHOD__);
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
;
SQL;

        $since = new DateTime();
        $since->sub(new DateInterval('P1D'))->setTime(0, 0);
        $params = array_fill(0, 5, $since->format(GK_DB_DATETIME_FORMAT));
        $this->console_writer->print(['sql', 'finding users', 'prepare'], true);
        $result = Base::instance()->get('DB')->exec($sql, $params);
        $this->user = new User();
        foreach ($result as $_userid) {
            $this->user->load(['id = ?', $_userid['userid']]);
            $this->console_writer->print([$this->user->id, $this->user->username, 'prepare']);
            $this->send();
        }
        $this->end();
    }

    public function sendUser(Base $f3) {
        $this->start(__METHOD__);
        $userid = $f3->get('PARAMS.userid');
        $this->console_writer->print([$userid, '', 'prepare']);
        $this->user = new User();
        $this->user->load(['id = ?', $userid]);
        if ($this->user->dry()) {
            $this->console_writer->print([$this->user->id, $this->user->username, '404 skipped'], true);

            return;
        }
        $this->send();
        $this->end();
    }

    protected function send() {
        if (!$this->user->hasEmail()) {
            $this->console_writer->print([$this->user->id, $this->user->username, '400 no email'], true);
            Event::instance()->emit('cron.dailymail.nomail', $this->user);

            return;
        }
        if (!$this->user->daily_mails) {
            $this->console_writer->print([$this->user->id, $this->user->username, '403 don\'t want'], true);
            Event::instance()->emit('cron.dailymail.deny', $this->user);

            return;
        }

        $this->email = new EmailDailyMail();

        try {
            $this->compute_since();
            $this->load_news();
            $this->load_last_moves_owned_geokrety();
            $this->load_last_moves_watched_geokrety();
            $this->load_moves_comments();
            $this->load_near_home_dropped_geokrety();

            if (!$this->updates_count) {
                $this->console_writer->print([$this->user->id, $this->user->username, '204 empty'], true);
                Event::instance()->emit('cron.dailymail.empty', $this->user);

                return;
            }

            $this->updates_count = 0;
            $this->console_writer->print([$this->user->id, $this->user->username, 'sending']);
            $this->email->sendDailyMail($this->user);
        } catch (Exception $e) {
            $this->console_writer->print([$this->user->id, $this->user->username, sprintf('500 error: %s', $e->getMessage())], true);
            Event::instance()->emit('cron.dailymail.error', $this->user);

            return;
        }
        $this->console_writer->print([$this->user->id, $this->user->username, '200 sent'], true);
        Event::instance()->emit('cron.dailymail.sent', $this->user);
    }

    private function compute_since() {
        $this->since = $this->user->last_mail_datetime;
        if (is_null($this->since) or $this->since->diff(new DateTime('now'))->format('%a') > 1) {
            $this->since = new DateTime();
            $this->since->sub(new DateInterval('P1D'));
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
     * @throws \PHPMailer\PHPMailer\Exception
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
    LIMIT 500
) as t;
SQL;
        $this->console_writer->print([$this->user->id, $this->user->username, 'load dropped - geojson']);
        $result = Base::instance()->get('DB')->exec($sql, [$this->user->id, $this->since->format(GK_DB_DATETIME_FORMAT)]);
        $geojson = ($result[0]['geojson']);

        $home = <<<'GEOJSON'
{
  "type": "Feature",
  "geometry": {
    "type": "Point",
    "coordinates": [ %s, %s]
  },
  "markerIconOptions": {
    "iconUrl": "%s/home48.png",
    "iconAnchor": [24, 24]
  }
}
GEOJSON;
        $home = sprintf($home, $this->user->home_longitude, $this->user->home_latitude, GK_CDN_ICONS_URL);

        $geojson = json_decode($geojson);
        array_unshift($geojson->features, json_decode($home, true));

        $img_url_params = http_build_query([
            //'center' => sprintf('%s,%s', $this->user->home_longitude, $this->user->home_latitude),
            'arrows' => true,
            'geojson' => json_encode($geojson),
            'width' => 640,
            'height' => 480,
            'oxipng' => true,
            'maxZoom' => 13,
            //'zoom' => 11,
            'markerIconOptions' => sprintf('{"iconUrl": "%s/pins/green.png", iconAnchor: [6, 20]}', GK_CDN_ICONS_URL),
        ]);
        $this->console_writer->print([$this->user->id, $this->user->username, 'load dropped - image']);
        try {
            $fp = fopen('php://memory', 'w');
            File::download(sprintf('%s?%s', GK_OSM_STATIC_MAPS_URI, $img_url_params), $fp);
            rewind($fp);
            $img_string = stream_get_contents($fp);
            fclose($fp);
        } catch (\Exception $e) {
            echo sprintf('E: Download static maps image failed: %s', $e->getMessage());

            return;
        }

        $img_cid = 'GK_NEAR_HOME_IMG';
        Smarty::assign('gk_near_home_img', $img_cid);
        $this->email->addStringEmbeddedImage($img_string, $img_cid, 'gk_near_home.png');
    }
}
