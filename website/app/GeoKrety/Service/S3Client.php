<?php

namespace GeoKrety\Service;

class S3Client extends \Prefab {
    private $s3;
    private $s3_public;

    public static function instance($public = false) {
        $self = parent::instance();

        if ($public) {
            return $self->s3_public;
        }

        return $self->s3;
    }

    public function __construct() {
        $this->s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => GK_MINIO_SERVER_URL,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => GK_MINIO_ACCESS_KEY,
                'secret' => GK_MINIO_SECRET_KEY,
            ],
        ]);
        $this->s3_public = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => GK_MINIO_SERVER_URL_EXTERNAL,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => GK_MINIO_ACCESS_KEY,
                'secret' => GK_MINIO_SECRET_KEY,
            ],
        ]);
    }
}
