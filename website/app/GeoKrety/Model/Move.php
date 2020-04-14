<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use GeoKrety\Service\HTMLPurifier;

class Move extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_moves';

    protected $fieldConf = [
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'geokret' => [
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
            'nullable' => false,
        ],
        'move_type' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'log_type',
        ],
        'username' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'anonymous_only_required|min_len,'.GK_USERNAME_MIN_LENGTH.'|max_len,'.GK_USERNAME_MAX_LENGTH,
            'filter' => 'trim|HTMLPurifier|EmptyString2Null',
        ],
        'lat' => [
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
            'validate' => 'float|logtype_require_coordinates',
        ],
        'lon' => [
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
            'validate' => 'float|logtype_require_coordinates',
        ],
        'alt' => [
            'type' => Schema::DT_INT2,
            'nullable' => true,
            'default' => '-32768',
        ],
        'country' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'distance' => [
            'type' => Schema::DT_INT4,
            'nullable' => true,
        ],
        'waypoint' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'filter' => 'trim|HTMLPurifier',
        ],
        'comment' => [
            'type' => Schema::DT_TEXT,
            'filter' => 'HTMLPurifier',
        ],
        'comments' => [
            'has-many' => ['\GeoKrety\Model\MoveComment', 'move'],
        ],
        'pictures_count' => [
            'type' => Schema::DT_TINYINT,
            'default' => 0,
        ],
        'pictures' => [
            'has-many' => ['\GeoKrety\Model\Picture', 'move'],
        ],
        'comments_count' => [
            'type' => Schema::DT_INT2,
            'nullable' => true,
        ],
        'app' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'max_len,16',
            'filter' => 'trim|HTMLPurifier',
        ],
        'app_ver' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'max_len,128',
            'filter' => 'trim|HTMLPurifier',
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'moved_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
            'validate' => 'required|not_in_the_future|after_geokret_birth|move_not_same_datetime',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
    ];

    public function set_comment($value) {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_move_type($value) {
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
            return [];
        }

        return [$this->lat, $this->lon];
    }

    public function get_created_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_moved_on_datetime($value) {
        return is_null($value) ? null : self::get_date_object($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function isAuthor() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->author) && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function getMoveOnPage() {
        $page = \Base::instance()->get('DB')->exec(
            'SELECT moves_get_on_page(?, ?, ?) AS page',
            [
                $this->id,
                GK_PAGINATION_GEOKRET_MOVES,
                $this->geokret->id,
            ]
        );

        return $page[0]['page'];
    }

    public function __construct() {
        parent::__construct();
        $this->beforesave(function (Move $self) {
            if (!$self->move_type->isCoordinatesRequired()) {
                $self->waypoint = null;
                $self->lat = null;
                $self->lon = null;
                $self->alt = null;
                $self->country = null;
                $self->distance = null;
            }
        });
        $this->beforeerase(function ($self) {
            // Dropping pictures here instead of relying on db Triggers will delete files on S3
            $pictureModel = new Picture();
            $pictures = $pictureModel->find(['move = ?', $self->id]);
            foreach ($pictures as $picture) {
                $picture->erase();
            }
        });
    }
}
