<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;
use GeoKrety\PictureType;
use GeoKrety\Service\S3Client;
use function Sentry\captureMessage;

/**
 * @property int|null id
 * @property int|User|null author
 * @property string|null bucket
 * @property string|null key
 * @property int|Move|null move
 * @property int|Geokret|null geokret
 * @property int|User|null user
 * @property string|null filename
 * @property string|null caption
 * @property DateTime created_on_datetime
 * @property DateTime|null used_on_datetime
 * @property DateTime|null uploaded_on_datetime
 * @property int|PictureType type
 * @property int pictures_count
 */
class Picture extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_pictures';

    protected $fieldConf = [
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'bucket' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty',
            'nullable' => true,
        ],
        'key' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty',
            'nullable' => true,
        ],
        'move' => [
            'belongs-to-one' => '\GeoKrety\Model\Move',
            'nullable' => true,
        ],
        'geokret' => [
            'belongs-to-one' => '\GeoKrety\Model\Geokret',
            'nullable' => true,
        ],
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => true,
        ],
        'filename' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'filter' => 'trim|HTMLPurifier',
        ],
        'caption' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'max_len,'.GK_PICTURE_CAPTION_MAX_LENGTH,
            'filter' => 'trim|HTMLPurifier',
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
        'uploaded_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'type' => [
            'type' => Schema::DT_TINYINT,
            'validate' => 'picture_type',
            'nullable' => false,
        ],
    ];

    public static function expireNeverUploaded() {
        $pictureModel = new Picture();
        $pictureModel->erase([
            'uploaded_on_datetime = ? AND created_on_datetime > NOW() - cast(? as interval)',
            null,
            GK_SITE_PICTURE_UPLOAD_DELAY_MINUTES.' DAY',
        ]);
    }

    public function set_type($value) {
        if (\is_int($value)) {
            return $value;
        }
        if (ctype_digit($value)) {
            return intval($value);
        }
        if (is_a($value, '\GeoKrety\PictureType')) {
            return $value->getTypeId();
        }

        return null;
    }

    public function set_geokret($value): int {
        return Geokret::gkid2id($value);
    }

    public function get_type($value): PictureType {
        return new \GeoKrety\PictureType($value);
    }

    public function get_created_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function get_uploaded_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    public function getBucketName(): string {
        return $this->type->getBucketName();
    }

    public function getThumbnailBucketName(): string {
        return $this->type->getThumbnailBucketName();
    }

    public function isAuthor(): bool {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->author) && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function isMainAvatar(): bool {
        if ($this->type->isType(PictureType::PICTURE_GEOKRET_AVATAR)) {
            return $this->geokret->avatar && $this->geokret->avatar->id === $this->id;
        }
        if ($this->type->isType(PictureType::PICTURE_USER_AVATAR)) {
            return $this->user->avatar && $this->user->avatar->id === $this->id;
        }
        if ($this->type->isType(PictureType::PICTURE_GEOKRET_MOVE)) {
            return $this->move->geokret->avatar && $this->move->geokret->avatar->id === $this->id;
        }

        return false;
    }

    public function isUploaded(): bool {
        return !is_null($this->uploaded_on_datetime);
    }

    public function isType($type): bool {
        return $this->type->isType($type);
    }

    public function hasPermissionOnParent(): bool {
        if ($this->isType(PictureType::PICTURE_GEOKRET_AVATAR)) {
            return $this->geokret->isOwner();
        }
        if ($this->isType(PictureType::PICTURE_USER_AVATAR)) {
            return $this->user->isCurrentUser();
        }
        if ($this->isType(PictureType::PICTURE_GEOKRET_MOVE)) {
            return $this->move->geokret->isOwner();
        }

        captureMessage('We should never reach there!');

        return false;
    }

    public function get_url(): string {
        if (is_null($this->bucket)) {
            return sprintf('https://cdn.geokrety.org/images/obrazki/%s', $this->filename);
        }

        return sprintf('%s/%s/%s', GK_MINIO_SERVER_URL_EXTERNAL, $this->type->getBucketName(), $this->key);
        // Not as performant as above ~5-10ms
        //$s3 = S3Client::instance()->getS3Public();
        //return $s3->getObjectUrl($this->type->getBucketName(), $this->key);
    }

    public function get_thumbnail_url(): string {
        if (is_null($this->bucket)) {
            return sprintf('https://cdn.geokrety.org/images/obrazki-male/%s', $this->filename);
        }

        return sprintf('%s/%s-thumbnails/%s', GK_MINIO_SERVER_URL_EXTERNAL, $this->type->getBucketName(), $this->key);
        // Not as performant as above ~5-10ms
        //$s3 = S3Client::instance()->getS3Public();
        //$bucketName = S3Client::getThumbnailBucketName($this->type->getBucketName());
        //return $s3->getObjectUrl($bucketName, $this->key);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->author = \Base::instance()->get('SESSION.CURRENT_USER');
        });
        $this->beforeerase(function ($self) {
            $s3 = S3Client::instance()->getS3();
            foreach ([$self->getBucketName(), $self->getThumbnailBucketName()] as $bucket) {
                $s3->deleteObject([
                    'Bucket' => $bucket,
                    'Key' => $self->key,
                ]);
            }
        });
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            // 'author' => $this->author->id ?? null,
            // 'bucket' => $this->bucket,
            // 'key' => $this->key,
            // 'move' => $this->move->id ?? null,
            // 'geokret' => $this->geokret->id ?? null,
            // 'user' => $this->user->id ?? null,
            // 'filename' => $this->filename,
            // 'caption' => $this->caption,
            // 'created_on_datetime' => $this->created_on_datetime,
            // 'used_on_datetime' => $this->used_on_datetime,
            // 'uploaded_on_datetime' => $this->uploaded_on_datetime,
            'type' => $this->type,
        ];
    }
}
