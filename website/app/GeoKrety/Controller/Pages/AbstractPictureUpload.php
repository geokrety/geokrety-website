<?php

namespace GeoKrety\Controller;

use Aws\S3\PostObjectV4;
use GeoKrety\Model\Picture;
use GeoKrety\Service\S3Client;
use Sugar\Event;

abstract class AbstractPictureUpload extends Base {
    private string $imgKey;

    public function request_s3_file_signature(\Base $f3) {
        header('Content-Type: application/json; charset=utf-8');

        $s3 = S3Client::instance()->getS3Public();

        $formInputs = [
            'acl' => 'private',
            's3Key' => $this->getImgKey(),
            'key' => $this->getFullImgKey(),
        ];

        // Related docs:
        // https://docs.aws.amazon.com/AmazonS3/latest/API/sigv4-HTTPPOSTConstructPolicy.html
        // https://docs.aws.amazon.com/AmazonS3/latest/dev/HTTPPOSTForms.html#HTTPPOSTConstructPolicy
        $options = [
            ['acl' => 'private'],
            ['bucket' => GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER],
            ['eq', '$key', $this->getFullImgKey()],
            ['content-length-range', 1024, 1024 * 1024 * GK_SITE_PICTURE_UPLOAD_MAX_FILESIZE],
//            ['starts-with', '$Content-Type', 'image/jpeg'], // TODO: This should work, but seems buggy in minio right now?
        ];
        $expires = sprintf('+%d minutes', GK_SITE_PICTURE_UPLOAD_DELAY_MINUTES);
        $postObject = new PostObjectV4(
            $s3,
            GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER,
            $formInputs,
            $options,
            $expires
        );
        $formAttributes = $postObject->getFormAttributes();
        $formInputs = $postObject->getFormInputs();

        $response = array_merge(
            [
                'success' => true,
                'uploadUrl' => $formAttributes['action'],
            ],
            $formInputs
        );

        $picture = $this->generatePictureObject($f3);
        if (!$picture->validate()) {
            http_response_code(400);
            $response = [
                'success' => 0,
                'text' => $f3->get('validation.error'),
            ];
            echo json_encode($response);
            exit;
        }

        try {
            $picture->save();
        } catch (\Exception $e) {
            $f3->get('DB')->rollback();
            http_response_code(400);
            $response = [
                'success' => 0,
                'text' => 'Failed to store upload url into database.',
            ];
            echo json_encode($response);
            exit;
        }

        Event::instance()->emit(sprintf('%s.presigned_request', $this->getEventNameBase()), $picture, $response);

        echo json_encode($response);
    }

    public function getImgKey() {
        if (!isset($this->imgKey)) {
            $this->imgKey = $this->_generateKey();
        }

        return $this->imgKey;
    }

    public function getFullImgKey() {
        return static::fullImgKey($this->getImgKey());
    }

    public static function fullImgKey(string $key) {
        return sprintf('%s/%s', static::getBucket(), $key);
    }

    public function generatePictureObject(\Base $f3): Picture {
        $picture = new Picture();
        $picture->bucket = $this->getBucket();
        $picture->key = $this->getImgKey();
        $picture->type = $this->getPictureType();
        if ($f3->exists('POST.filename')) {
            $picture->filename = $f3->get('POST.filename');
        }
        $picture->caption = null;
        $this->setRelationships($picture);

        return $picture;
    }

    abstract protected function _generateKey(): string;

    abstract public static function getBucket(): string;

    abstract public function getEventNameBase(): string;

    abstract public function getPictureType(): int;

    abstract public function setRelationships(Picture $picture): void;
}
