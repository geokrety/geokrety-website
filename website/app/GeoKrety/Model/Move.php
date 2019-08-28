<?php

namespace GeoKrety\Model;

use GeoKrety\Service\HTMLPurifier;
use GeoKrety\Service\WaypointInfo;
use GeoKrety\LogType;
use DB\SQL\Schema;

class Move extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-ruchy';

    protected $fieldConf = array(
        'author' => array(
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ),
        'geokret' => array(
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
            'nullable' => false,
        ),
        'logtype' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'log_type',
        ),
        'username' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'anonymous_only_required|min_len,'.GK_USERNAME_MIN_LENGTH.'|max_len,'.GK_USERNAME_MAX_LENGTH,
            'filter' => 'trim|HTMLPurifier',
        ),
        'lat' => array(
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
            'validate' => 'float|logtype_require_coordinates',
        ),
        'lon' => array(
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
            'validate' => 'float|logtype_require_coordinates',
        ),
        'alt' => array(
            'type' => Schema::DT_INT2,
            'nullable' => true,
            'default' => '-32768',
        ),
        'country' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'distance' => array(
            'type' => Schema::DT_INT4,
            'nullable' => true,
        ),
        'waypoint' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'filter' => 'trim|HTMLPurifier',
        ),
        'comment' => array(
            'type' => Schema::DT_TEXT,
            'filter' => 'HTMLPurifier',
        ),
        'comments' => array(
            'has-many' => array('\GeoKrety\Model\MoveComment', 'move'),
        ),
        'pictures_count' => array(
            'type' => Schema::DT_INT1,
            'nullable' => true,
        ),
        'comments_count' => array(
            'type' => Schema::DT_INT2,
            'nullable' => true,
        ),
        'app' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'max_len,16',
            'filter' => 'trim|HTMLPurifier',
        ),
        'app_ver' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'max_len,16',
            'filter' => 'trim|HTMLPurifier',
        ),
        'created_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ),
        'moved_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
            'validate' => 'not_in_the_future|after_geokret_birth|move_not_same_datetime',
        ),
        'updated_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ),
    );

    public function set_comment($value) {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_comment($value) {
        // Workaround historical database modifications
        $txt = str_replace('<br />', '  ', $value);
        $txt = str_replace('[<a href=\'', '', $txt);
        $txt = str_replace('\' rel=nofollow>Link</a>]', '', $txt);

        return HTMLPurifier::getPurifier()->purify($txt);
    }

    public function get_username($value) {
        return html_entity_decode($value);
    }

    public function get_logtype($value) {
        return new \GeoKrety\LogType($value);
    }

    public function get_lat($value) {
        return $value ? number_format(floatval($value), 5, '.', '') : $value;
    }

    public function get_lon($value) {
        return $value ? number_format(floatval($value), 5, '.', '') : $value;
    }

    public function get_coordinates($value) {
        if (is_null($this->lat) || is_null($this->lon)) {
            return;
        }

        return sprintf('%.04f %.04f', $this->lat, $this->lon);
    }

    public function get_point() {
        if (is_null($this->lat) || is_null($this->lon)) {
            return array();
        }

        return array($this->lat, $this->lon);
    }

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_moved_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isAuthor() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function getMoveOnPage() {
        $perPage = GK_PAGINATION_GEOKRET_MOVES;
        $table = $this->table;
        $sql = <<<EOQUERY
SELECT  CEILING(gkmoves.rank / $perPage) AS page
FROM    (SELECT     @rank:=@rank+1 AS rank, id
         FROM       `$table` AS ru, (SELECT @rank:=0) t2
         WHERE      geokret = ?
         ORDER BY   ru.moved_on_datetime DESC
         ) AS gkmoves
WHERE    gkmoves.id = ?
EOQUERY;

        $page = \Base::instance()->get('DB')->exec(
            $sql,
            array(
                $this->geokret->id,
                $this->id,
            )
        );

        return $page[0]['page'];
    }

    private function findNext($move) {
        $next = new Move();
        $filter = array(
            'id != ? AND geokret = ? AND logtype IN ? AND moved_on_datetime > ?',
            $move->id,
            $move->geokret->id,
            array_map('strval', LogType::LOG_TYPES_COUNT_KILOMETERS),
            $move->moved_on_datetime->format('Y-m-d H:i:s'),
        );
        $options = array(
            'limit' => 1,
            'order' => 'moved_on_datetime ASC',
        );
        $next->load($filter, $options);

        return $next;
    }

    private function findPrev($move) {
        $prev = new Move();
        $filter = array(
            'id != ? AND geokret = ? AND logtype IN ? AND moved_on_datetime < ?',
            $move->id,
            $move->geokret->id,
            array_map('strval', LogType::LOG_TYPES_COUNT_KILOMETERS),
            $move->moved_on_datetime->format('Y-m-d H:i:s'),
        );
        $options = array(
            'limit' => 1,
            'order' => 'moved_on_datetime DESC',
        );
        $prev->load($filter, $options);

        return $prev;
    }

    private function computeDistanceMoveNext() {
        // Find move just after

        $next = $this->findNext($this);
        if ($next->dry()) {
            // There was no move after, abort…
            return;
        }
        // If my new move type doesn't require coordinates, then the reference
        // point is not me but point just before
        $ref = $this;
        if (!$this->logtype->isCoordinatesRequired()) {
            $ref = $this->findPrev($this);
            if ($ref->dry()) {
                // There was no move after, reset the next…
                $next->distance = 0;
                $next->save();

                return;
            }
        }
        // Compute distance
        $coordA = new \League\Geotools\Coordinate\Coordinate($ref->point);
        $coordB = new \League\Geotools\Coordinate\Coordinate($next->point);
        $geotools = new \League\Geotools\Geotools();
        $distance = $geotools->distance()->setFrom($coordA)->setTo($coordB);
        $next->distance = $distance->in('km')->haversine();
        $next->save();
    }

    private function computeDistance() {
        // Find move just before
        $prev = $this->findPrev($this);
        if ($prev->dry()) {
            // There was no move before
            $this->distance = 0;

            return;
        }
        // Compute distance
        $coordA = new \League\Geotools\Coordinate\Coordinate($this->point);
        $coordB = new \League\Geotools\Coordinate\Coordinate($prev->point);
        $geotools = new \League\Geotools\Geotools();
        $distance = $geotools->distance()->setFrom($coordA)->setTo($coordB);
        $this->distance = $distance->in('km')->haversine();
    }

    private function computeDistanceMoveNextOldPlace() {
        // Find move just after, but at the old place in history (if edit of course)
        if (!$this->id) {
            return;
        }
        $current = new Move();
        $current->load(array('id = ?', $this->id));
        if (empty($current->point)) {
            return;
        }

        $next = $this->findNext($this);
        if ($next->dry()) {
            // There was no move after, abort…
            return;
        }
        // Compute distance
        $coordA = new \League\Geotools\Coordinate\Coordinate($current->point);
        $coordB = new \League\Geotools\Coordinate\Coordinate($next->point);
        $geotools = new \League\Geotools\Geotools();
        $distance = $geotools->distance()->setFrom($coordA)->setTo($coordB);
        $next->distance = $distance->in('km')->haversine();
        $next->save();
    }

    private function updateGeokretLastPosition(&$geokret) {
        $lastPosition = new Move();
        $filter = array(
            'geokret = ? AND logtype IN ?',
            $geokret->id,
            array_map('strval', LogType::LOG_TYPES_LAST_POSITION),
        );
        $options = array(
            'order' => 'moved_on_datetime DESC',
        );
        $lastPosition->load($filter, $options);
        $geokret->last_position = $lastPosition->id;
    }

    private function updateGeokretLastMove(&$geokret) {
        $lastMove = new Move();
        $filter = array(
            'geokret = ?',
            $geokret->id,
        );
        $options = array(
            'order' => 'moved_on_datetime DESC',
        );
        $lastMove->load($filter, $options);
        $geokret->last_log = $lastMove->id;
    }

    private function updateGeokretStats(&$geokret) {
        $moveStats = \Base::instance()->get('DB')->exec(
            'SELECT COUNT(*) AS count, COALESCE(SUM(distance), 0) AS distance FROM `'.$this->table.'` WHERE geokret = ?',
            array(
                $geokret->id,
            )
        );
        $geokret->caches_count = $moveStats[0]['count'];
        $geokret->distance = $moveStats[0]['distance'];
    }

    private function updateGeokretMissingStatus(&$geokret) {
        $lastPosition = new Move();
        $filter = array(
            'geokret = ? AND logtype IN ?',
            $geokret->id,
            array_map('strval', LogType::LOG_TYPES_ALIVE),
        );
        $options = array(
            'order' => 'moved_on_datetime DESC',
        );
        $lastPosition->filter('comments', array('type = 1'));
        $lastPosition->countRel('comments');
        $lastPosition->load($filter, $options);
        $geokret->missing = $lastPosition->count_comments > 0;
    }

    private function addWaypoint() {
        if (!WaypointInfo::isGC($this->waypoint)) {
            return;
        }
        $waypoint = new Waypoint();
        $waypoint->load(array('waypoint = ?', $this->waypoint));
        $waypoint->waypoint = $this->waypoint;
        $waypoint->lat = $this->lat;
        $waypoint->lon = $this->lon;
        $waypoint->link = WaypointInfo::getLink($this->waypoint);
        $waypoint->save();
    }

    private function removeMissingStatus() {
        // TODO, why not change the type and update the comment ???
        $comment = new MoveComment();
        $comment->erase(array('move = ? AND type = 1', $this->id));
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->touch('created_on_datetime');
            $self->touch('moved_on_datetime');
        });
        $this->beforesave(function ($self) {
            // Force reset fields if coordinates not required
            if ($self->logtype->isCountingKilometers()) {
                $self->computeDistance(); // This move
            }
            $self->computeDistanceMoveNext(); // The move after
            $self->computeDistanceMoveNextOldPlace(); // The move after at the old place
            if (!$self->logtype->isCoordinatesRequired()) {
                $self->waypoint = null;
                $self->lat = null;
                $self->lon = null;
                $self->alt = null;
                $self->country = null;
                $self->distance = null;
            }
        });
        $this->aftersave(function ($self) {
            $geokret = new Geokret();
            $geokret->load(array('id = ?', $self->geokret->id));
            $self->updateGeokretStats($geokret);
            $self->updateGeokretLastPosition($geokret);
            $self->updateGeokretLastMove($geokret);
            $self->updateGeokretMissingStatus($geokret);
            $geokret->save();

            if ($self->logtype->isCoordinatesRequired()) {
                $self->addWaypoint();
            }

            if (!$self->logtype->isTheoricallyInCache()) {
                $self->removeMissingStatus();
            }
        });
    }
}
