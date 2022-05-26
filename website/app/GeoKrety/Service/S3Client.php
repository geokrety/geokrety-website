<?php

namespace GeoKrety\Service;

use Aws\S3\S3Client as AWSS3Client;

class S3Client extends \Prefab {
    private AWSS3Client $s3;
    private AWSS3Client $s3_public;

    public function getS3(): AWSS3Client {
        return $this->s3;
    }

    public function getS3Public(): AWSS3Client {
        return $this->s3_public;
    }

    public function __construct() {
        $this->s3 = new AWSS3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => GK_MINIO_SERVER_URL,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => MINIO_ACCESS_KEY,
                'secret' => MINIO_SECRET_KEY,
            ],
        ]);
        $this->s3_public = new AWSS3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => GK_MINIO_SERVER_URL_EXTERNAL,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => MINIO_ACCESS_KEY,
                'secret' => MINIO_SECRET_KEY,
            ],
        ]);
    }

    public static function getThumbnailBucketName($bucket) {
        return sprintf('%s-thumbnails', $bucket);
    }
}
