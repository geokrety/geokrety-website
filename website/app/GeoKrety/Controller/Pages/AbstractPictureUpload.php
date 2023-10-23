<?php

namespace GeoKrety\Controller;

use Aws\S3\PostObjectV4;
use GeoKrety\Model\Picture;
use GeoKrety\Service\RateLimit;
use GeoKrety\Service\S3Client;
use GeoKrety\Service\Xml\Error;
use GeoKrety\Service\Xml\Generic;
use Sugar\Event;

class UploadPermissionException extends \Exception {
}

abstract class AbstractPictureUpload extends Base {
    private string $imgKey;

    public const CONTENT_TYPE_JSON = 'Content-Type: application/json; charset=utf-8';
    public const CONTENT_TYPE_XML = 'Content-Type: application/xml; charset=utf-8';

    private function wanted_response_content_type(\Base $f3) {
        // Requests having secid are known to be XML
        if ($f3->exists('REQUEST.secid')) {
            return self::CONTENT_TYPE_XML;
        }

        // Default client is dropzone and is talking json
        return self::CONTENT_TYPE_JSON;
    }

    private function set_response_content_type(\Base $f3) {
        header($this->wanted_response_content_type($f3));
    }

    private function render_response(\Base $f3, array $response) {
        if ($this->wanted_response_content_type($f3) === self::CONTENT_TYPE_XML) {
            return Generic::buildGeneric(true, 'image-upload', $response);
        }
        // Default is Json
        return json_encode($response);
    }

    /**
     * Request a presigned url from the xml api
     * Authentication is done by using a secid.
     *
     * @return void
     */
    public function request_s3_file_signature_api(\Base $f3) {
        $this->set_response_content_type($f3);
        $this->authenticate_via_secid($f3);
        RateLimit::check_rate_limit_raw('API_V1_REQUEST_S3_FILE_SIGNATURE', $this->f3->get('REQUEST.secid'));
        $data = $this->request_s3_file_signature($f3);
        // Remove some dropzone internal headers on the public API
        foreach (['success', 'uploadUrl', 's3Key'] as $key) {
            $response[$key] = $data[$key];
            unset($data[$key]);
        }
        $response = array_merge($response, ['headers' => $data]);
        echo $this->render_response($f3, $response);
    }

    /**
     * Request a presigned url from dropzone
     * Authentication is done by the framework.
     *
     * @return void
     */
    public function request_s3_file_signature_ajax(\Base $f3) {
        $this->set_response_content_type($f3);
        $response = $this->request_s3_file_signature($f3);
        echo $this->render_response($f3, $response);
    }

    private function request_s3_file_signature(\Base $f3) {
        try {
            $this->check_permission($f3);
        } catch (UploadPermissionException $e) {
            http_response_code(403);
            Error::buildError(true, [$e->getMessage()]);
            exit;
        }

        $s3 = S3Client::instance()->getS3Public();

        $formInputs = [
            'key' => $this->getFullImgKey(),
        ];

        // Related docs:
        // https://docs.aws.amazon.com/AmazonS3/latest/API/sigv4-HTTPPOSTConstructPolicy.html
        // https://docs.aws.amazon.com/AmazonS3/latest/dev/HTTPPOSTForms.html#HTTPPOSTConstructPolicy
        $options = [
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
                's3Key' => $this->getImgKey(),
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

            return $response;
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

            return $response;
        }

        Event::instance()->emit(sprintf('%s.presigned_request', $this->getEventNameBase()), $picture, $response);

        return $response;
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

    /**
     * Check if the current user has permission on this object.
     *
     * @return void
     *
     * @throws UploadPermissionException
     */
    abstract protected function check_permission(\Base $f3);
}
