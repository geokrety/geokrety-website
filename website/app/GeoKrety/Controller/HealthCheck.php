<?php

namespace GeoKrety\Controller;

use Aws\S3\Exception\S3Exception;
use GeoKrety\HealthState;
use GeoKrety\Service\Config;
use GeoKrety\Service\S3Client;

class HealthCheck extends Base {
    protected $state;

    public function __construct() {
        $this->state = new HealthState();
    }

    public function get(\Base $f3) {
        $this->checkWebsiteConfig();
        $this->checkWebsiteDatabase($f3);
        $this->checkDirectoriesPermissions();
        $this->checkS3Server();

        echo $this->state->render();
    }

    private function checkWebsiteConfig() {
        $websiteConfig = Config::instance();
        $validationDetails = null;
        $isConfigOk = $websiteConfig->isValid();
        if (!$isConfigOk) {
            $validationDetails = $websiteConfig->validationDetails();
        }
        $this->state->setDependencyState('config', $isConfigOk ? HealthState::HEALTH_STATE_OK : HealthState::HEALTH_STATE_KO, $validationDetails);
    }

    private function checkWebsiteDatabase(\Base $f3) {
        $isDbOk = $f3->get('DB') !== false;
        $this->state->setDependencyState('db', $isDbOk ? HealthState::HEALTH_STATE_OK : HealthState::HEALTH_STATE_KO);
    }

    private function checkDirectoriesPermissions() {
        $this->state->setDependencyState('assets-dir-perm', is_writable(GK_F3_ASSETS_PUBLIC) ? HealthState::HEALTH_STATE_OK : HealthState::HEALTH_STATE_KO, sprintf('%s must be writable by php', realpath(GK_F3_ASSETS_PUBLIC)));
    }

    private function checkS3Server() {
        if (is_null(MINIO_ACCESS_KEY)) {
            // Skip test if s3 is not configured
            return;
        }
        $s3BucketList = [
            GK_BUCKET_NAME_STATPIC,
            GK_BUCKET_NAME_GEOKRETY_AVATARS,
            sprintf('%s-thumbnails', GK_BUCKET_NAME_GEOKRETY_AVATARS),
            GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER,
            GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER,
        ];
        $s3 = new S3Client();

        foreach ($s3BucketList as $bucket) {
            $stateName = "s3-bucket-$bucket";

            try {
                $s3->getS3()->headBucket(['Bucket' => $bucket]);
                $this->state->setDependencyState($stateName, HealthState::HEALTH_STATE_OK);
            } catch (S3Exception $e) {
                $this->state->setDependencyState($stateName, HealthState::HEALTH_STATE_KO);
            }
        }
    }
}
