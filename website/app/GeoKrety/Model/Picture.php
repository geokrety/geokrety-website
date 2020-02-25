<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use GeoKrety\Service\S3Client;

class Picture extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-pictures';

    protected $fieldConf = [
        'author' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'nullable' => false,
        ],
        'bucket' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty',
            'nullable' => false,
        ],
        'key' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty',
            'nullable' => false,
        ],
        'created_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'uploaded_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => true,
        ],
        'caption' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'max_len,'.GK_PICTURE_CAPTION_MAX_LENGTH,
            'filter' => 'trim|HTMLPurifier',
        ],
        'type' => [
            'type' => Schema::DT_TINYINT,
            'validate' => 'picture_type',
            'nullable' => false,
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
        // Legacy filename, TODO need migration to S3
        'filename' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'filter' => 'trim|HTMLPurifier',
        ],
    ];

    // TODO: validate that at least `move` or `geokret` or `user` is filled

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

    public function set_geokret($value) {
        return Geokret::gkid2id($value);
    }

    public function get_type($value) {
        return new \GeoKrety\PictureType($value);
    }

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_uploaded_on_datetime($value) {
        return self::get_date_object($value);
    }

    public static function expireNeverUploaded() {
        $pictureModel = new Picture();
        $pictureModel->erase([
            'uploaded_on_datetime = ? AND NOW() > DATE_ADD(created_on_datetime, INTERVAL ? MINUTE)',
            null,
            GK_SITE_PICTURE_UPLOAD_DELAY_MINUTES,
        ]);
    }

    public function isAuthor() {
        $f3 = \Base::instance();

        return $f3->get('SESSION.CURRENT_USER') && !is_null($this->author) && $f3->get('SESSION.CURRENT_USER') === $this->author->id;
    }

    public function isGeokretMainAvatar() {
        return $this->geokret->avatar && $this->geokret->avatar->id === $this->id;
    }

    public function get_url() {
        $s3 = S3Client::instance()->getS3Public();
        $publicUrl = $s3->getObjectUrl(GK_BUCKET_NAME_GEOKRETY_AVATARS, $this->key);

        return $publicUrl;
    }

    public function get_thumbnail_url() {
        $s3 = S3Client::instance()->getS3Public();
        $bucketName = S3Client::getThumbnailBucketName(GK_BUCKET_NAME_GEOKRETY_AVATARS);
        $publicUrl = $s3->getObjectUrl($bucketName, $this->key);

        return $publicUrl;
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->author = \Base::instance()->get('SESSION.CURRENT_USER');
        });
    }
}
