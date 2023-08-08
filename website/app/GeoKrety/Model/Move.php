<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use GeoKrety\LogType;
use GeoKrety\Service\HTMLPurifier;

/**
 * @property int|null id
 * @property int|Geokret geokret
 * @property float|null lat
 * @property float|null lon
 * @property int|null elevation
 * @property string|null country
 * @property int|null distance
 * @property string|null waypoint
 * @property int|User|null author
 * @property string|null comment
 * @property int pictures_count
 * @property int comments_count
 * @property string|null username
 * @property string|null app
 * @property string|null app_ver
 * @property \DateTime created_on_datetime
 * @property \DateTime moved_on_datetime
 * @property \DateTime updated_on_datetime
 * @property int|LogType move_type
 * @property string|null position
 * @property string|null reroute_url
 */
class Move extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_moves';

    protected $fieldConf = [
        'geokret' => [
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
            'nullable' => false,
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
        'position' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'alt' => [
            'type' => Schema::DT_INT,
            'nullable' => true,
            'default' => '-32768',
        ],
        'country' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'distance' => [
            'type' => Schema::DT_INT,
            'nullable' => true,
        ],
        'waypoint' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'filter' => 'trim|HTMLPurifier|EmptyString2Null',
        ],
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'comment' => [
            'type' => Schema::DT_TEXT,
            'filter' => 'HTMLPurifier',
            'nullable' => true,
        ],
        'comments' => [
            'has-many' => ['\GeoKrety\Model\MoveComment', 'move'],
            'validate' => 'max_len,'.GK_MOVE_COMMENT_MAX_LENGTH, // TODO <--- does this makes sense?
            'validate_level' => 3,
        ],
        'pictures_count' => [
            'type' => Schema::DT_SMALLINT,
            'default' => 0,
        ],
        'pictures' => [
            'has-many' => ['\GeoKrety\Model\Picture', 'move'],
            'validate_level' => 3,
        ],
        'comments_count' => [
            'type' => Schema::DT_INT,
            'default' => 0,
        ],
        'username' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'anonymous_only_required|min_len,'.GK_USERNAME_MIN_LENGTH.'|max_len,'.GK_USERNAME_MAX_LENGTH,
            'filter' => 'trim|HTMLPurifier|EmptyString2Null',
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
            'validate' => 'is_date',
        ],
        'moved_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
            'validate' => 'required|is_date|not_in_the_future|after_geokret_birth|move_not_same_datetime',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
        'move_type' => [
            'type' => Schema::DT_SMALLINT,
            'nullable' => true,
            'validate' => 'log_type',
        ],
    ];

    public function set_comment($value): string {
        return HTMLPurifier::getPurifier()->purify($value);
    }

    public function get_move_type($value): LogType {
        return new LogType($value);
    }

    public function get_lat($value) {
        return $value ? number_format(floatval($value), 5, '.', '') : $value;
    }

    public function get_lon($value) {
        return $value ? number_format(floatval($value), 5, '.', '') : $value;
    }

    public function get_coordinates(): string {
        if (is_null($this->lat) || is_null($this->lon)) {
            return '';
        }

        return sprintf('%.05f %.05f', $this->lat, $this->lon);
    }

    public function get_point(): array {
        if (is_null($this->lat) || is_null($this->lon)) {
            return [];
        }

        return [$this->lat, $this->lon];
    }

    public function get_created_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_moved_on_datetime($value): ?\DateTime {
        return is_null($value) ? null : self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_reroute_url($value): ?string {
        if (is_null($this->id)) {
            return null;
        }

        return sprintf('@geokret_details_paginate(@gkid=%s,@page=%d)#log%d', $this->geokret->gkid, $this->getMoveOnPage(), $this->id);
    }

    public function isAuthor(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->author) && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function isGeoKretLastPosition(): bool {
        return $this->geokret->last_position->id === $this->id;
    }

    public function getMoveOnPage(): int {
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
            foreach ($pictures ?: [] as $picture) {
                $picture->erase();
            }
        });
        $this->afterinsert(function ($self) {
            \Sugar\Event::instance()->emit('move.created', $self);
        });
        $this->afterupdate(function ($self) {
            \Sugar\Event::instance()->emit('move.updated', $self);
        });
        $this->aftererase(function ($self) {
            \Sugar\Event::instance()->emit('move.deleted', $self);
        });
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'geokret' => $this->geokret->id,
             'lat' => $this->lat,
             'lon' => $this->lon,
             'elevation' => $this->elevation,
             'country' => $this->country,
             'distance' => $this->distance,
            // 'waypoint' => $this->waypoint,
            // 'author' => $this->author->id ?? null,
            // 'comment' => $this->comment,
            // 'pictures_count' => $this->pictures_count,
            // 'comments_count' => $this->comments_count,
            // 'username' => $this->username,
            // 'app' => $this->app,
            // 'app_ver' => $this->app_ver,
            // 'created_on_datetime' => $this->created_on_datetime,
             'moved_on_datetime' => $this->moved_on_datetime->format('c'),
            // 'updated_on_datetime' => $this->updated_on_datetime,
            'move_type' => $this->move_type->getLogTypeId(),
            'move_type_name' => $this->move_type->getLogTypeString(),
            // 'position' => $this->position,
        ];
    }
}
