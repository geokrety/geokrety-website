<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use GeoKrety\GeokretyType;
use GeoKrety\LogType;

/**
 * @property int|null id
 * @property string gkid
 * @property string tracking_code
 * @property string name
 * @property string url
 * @property string|null mission
 * @property int|User|null owner
 * @property string|null owner_username
 * @property int distance
 * @property int caches_count
 * @property int pictures_count
 * @property Move[]|null moves
 * @property int|Move|null last_position
 * @property int|Move|null last_log
 * @property int|User|null holder
 * @property string|null holder_username
 * @property int|Picture|null avatar
 * @property DateTime created_on_datetime
 * @property DateTime updated_on_datetime
 * @property bool missing
 * @property int|GeokretyType type
 * @property int|Label label_template
 */
class Geokret extends Base {
    use \Validation\Traits\CortexTrait;

    public const GEOKRETY_PRESENT_IN_CACHE = 0;
    public const GEOKRETY_MISSING_IN_CACHE = 1;

    protected $db = 'DB';
    protected $table = 'gk_geokrety';

    protected $fieldConf = [
        'gkid' => [
            'type' => Schema::DT_INT4,
        ],
        'tracking_code' => [
            'type' => Schema::DT_VARCHAR128,
            // 'validate' => 'required',
            'nullable' => true,
        ],
        'name' => [
            'type' => Schema::DT_VARCHAR128,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty|min_len,'.GK_GEOKRET_NAME_MIN_LENGTH.'|max_len,'.GK_GEOKRET_NAME_MAX_LENGTH,
        ],
        'type' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'geokrety_type',
        ],
        'mission' => [
            'type' => Schema::DT_TEXT,
            'filter' => 'HTMLPurifier',
            'nullable' => true,
        ],
        'distance' => [
            'type' => Schema::DT_BIGINT,
            'default' => 0,
        ],
        'caches_count' => [
            'type' => Schema::DT_INT,
            'default' => 0,
        ],
        'pictures_count' => [
            'type' => Schema::DT_TINYINT,
            'default' => 0,
        ],
        'missing' => [
            'type' => Schema::DT_BOOLEAN,
            'default' => false,
        ],
        'label_template' => [
            'belongs-to-one' => '\GeoKrety\Model\Label',
            'nullable' => true,
            'validate' => 'is_not_false',
        ],
        'owner' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'holder' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'moves' => [
            'has-many' => ['\GeoKrety\Model\Move', 'geokret'],
        ],
        'owner_codes' => [
            'has-many' => ['\GeoKrety\Model\OwnerCode', 'geokret'],
        ],
        'watchers' => [
            'has-many' => ['\GeoKrety\Model\Watched', 'geokret'],
        ],
        'avatar' => [
            'belongs-to-one' => '\GeoKrety\Model\Picture',
            'nullable' => true,
        ],
        'avatars' => [
            'has-many' => ['\GeoKrety\Model\Picture', 'geokret'],
        ],
        'last_position' => [
            'belongs-to-one' => '\GeoKrety\Model\Move',
        ],
        'last_log' => [
            'belongs-to-one' => '\GeoKrety\Model\Move',
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
//            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
            'validate' => 'is_date',
        ],
    ];

    public function __construct() {
        parent::__construct();
        $this->afterinsert(function ($self) {
            \Sugar\Event::instance()->emit('geokret.created', $self);
        });
        $this->afterupdate(function ($self) {
            \Sugar\Event::instance()->emit('geokret.updated', $self);
        });
        $this->aftererase(function ($self) {
            \Sugar\Event::instance()->emit('geokret.deleted', $self);
        });
    }

    public static function gkid2id($gkid): ?int {
        if (\is_int($gkid)) {
            return $gkid;
        }
        if (ctype_digit($gkid)) {
            return \intval($gkid);
        }
        if (strtoupper(substr($gkid, 0, 2)) === 'GK') {
            $id_ = substr($gkid, 2);
            if (trim($id_, '0..9A..Fa..f') === '') {
                return hexdec($id_);
            }
        }

        return null;
    }

    public static function tracking_code_to_id($tracking_code): ?int {
        $resp = \Base::instance()->get('DB')->exec('SELECT gkid FROM gk_geokrety WHERE tracking_code = ?', [$tracking_code]);
        if (!sizeof($resp)) {
            return null;
        }

        return $resp[0]['gkid'];
    }

    public static function generate(): void {
        $faker = \Faker\Factory::create();

        $userCount = \Base::instance()->get('DB')->exec('SELECT COUNT(*) AS count FROM gk_users')[0]['count'];

        $geokret = new self();
        $geokret->name = $faker->sentence($nbWords = 2, $variableNbWords = true);
        $geokret->type = $faker->randomElement($array = GeokretyType::GEOKRETY_TYPES);
        $geokret->mission = $faker->paragraphs($nb = 3, $asText = true);
        $geokret->owner = $faker->numberBetween(1, $userCount);
        $geokret->save();
    }

    public function gkid(): string {
        return hexdec(substr($this->gkid, 2));
    }

    public function get_gkid($value): ?string {
        if (is_null($value)) {
            return null;
        }

        return self::id2gkid($value);
    }

    public static function id2gkid(int $id): ?string {
        return sprintf('GK%04X', $id);
    }

    public function get_name($value): string {
        return html_entity_decode($value);
    }

    public function get_tracking_code($value): string {
        return strtoupper($value);
    }

    public function get_type($value): GeokretyType {
        return new \GeoKrety\GeokretyType($value);
    }

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_url(): string {
        return \Base::instance()->alias('geokret_details', '@gkid='.$this->gkid);
    }

    public function hasTouchedInThePast(): bool {
        $f3 = \Base::instance();
        if (!$f3->get('SESSION.CURRENT_USER')) {
            return false;
        }

        if ($this->isOwner() || $this->isHolder()) {
            return true;
        }

        // TODO, speedup this using a special automanaged table
        $move = new Move();

        return $move->count(['author = ? AND geokret = ? AND move_type IN ?', $f3->get('SESSION.CURRENT_USER'), $this->id, LogType::LOG_TYPES_USER_TOUCHED], null, 0) > 0;
    }

    public function addFilterHasTouchedInThePast(User $user, array $gk_list) {
        // TODO, speedup this using a special automanaged table
        $this->orHas('moves', ['author = ? AND move_type IN ? AND geokret IN ?', $user->id, LogType::LOG_TYPES_USER_TOUCHED, $gk_list]);
    }

    /**
     * Check if the current logged in user is the GeoKret owner.
     */
    public function isOwner(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->owner) && $f3->get('SESSION.CURRENT_USER') === $this->owner->id;
    }

    /**
     * Check if the current logged in user is watching this GeoKret.
     */
    public function isWatching(): bool {
        $f3 = \Base::instance();
        $watch = new Watched();

        return $watch->count(['user = ? AND geokret = ?', $f3->get('SESSION.CURRENT_USER'), $this->id], null, 0);
    }

    public function isHolder(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->holder) && $f3->get('SESSION.CURRENT_USER') === $this->holder->id;
    }

    public function isArchived(): bool {
        return !is_null($this->last_position) && $this->last_position->move_type->isType(LogType::LOG_TYPE_ARCHIVED);
    }

    public function isMissing(): bool {
        return $this->missing;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'gkid' => $this->gkid,
            // 'tracking_code' => $this->tracking_code,
            // 'name' => $this->name,
            // 'mission' => $this->mission,
            // 'owner' => $this->owner->id ?? null,
            // 'distance' => $this->distance,
            // 'caches_count' => $this->caches_count,
            // 'pictures_count' => $this->pictures_count,
            // 'last_position' => $this->last_position->id ?? null,
            // 'last_log' => $this->last_log->id ?? null,
            // 'holder' => $this->holder->id ?? null,
            // 'avatar' => $this->avatar->id ?? null,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            // 'missing' => $this->missing,
            // 'label_template' => $this->label_template,
            'type' => $this->type->getTypeId(),
        ];
    }
}
