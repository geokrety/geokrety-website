<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\Base;
use GeoKrety\Model\Picture;
use GeoKrety\PictureType;
use GeoKrety\Service\S3Client;

class PicturesImporter extends BaseCleaner {
    private float $transferPercent;
    private string $trafficWay;
    private string $downloadFilename;
    protected string $class_name = __CLASS__;

    protected function getModel(): Base {
        return new Picture();
    }

    /**
     * Disable transaction for this, as minio webhook
     * need status recorder here
     * @return bool
     */
    public function useTransation(): bool {
        return false;
    }

    protected function getModelName(): string {
        return 'Pictures';
    }

    protected function getParamId(\Base $f3): int {
        return $f3->get('PARAMS.pictureid');
    }

    protected function filterHook(): array {
        return ['bucket = ? AND key = ?', null, null];
    }

    protected function orderHook(): array {
        return ['order' => 'created_on_datetime ASC'];
    }

    protected function process($object): void {
        $this->downloadFilename = $object->filename;
        $fileContent = $this->downloadFile("https://cdn.geokrety.org/images/obrazki/$object->filename");
        $this->uploadFile($object, $fileContent);
        $this->processResult(true);
    }

    protected function downloadFile($url) {
        $this->trafficWay = "\e[0;32m↓\e[0m";  // ↑↓
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [$this, 'progress']);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false); // needed to make progress function work
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, GK_SITE_USER_AGENT);
        $filecontent = curl_exec($ch);
        curl_close($ch);

        return $filecontent;
    }

    protected function uploadFile(Base $object, $fileContent) {
        $uploader = null;
        $id = null;

        if ($object->isType(PictureType::PICTURE_GEOKRET_AVATAR)) {
            $uploader = 'GeoKrety\Controller\GeokretAvatarUpload';
            $id = $object->geokret->gkid;
            $object->author = $object->geokret->owner;
        } elseif ($object->isType(PictureType::PICTURE_USER_AVATAR)) {
            $uploader = 'GeoKrety\Controller\UserAvatarUpload';
            $id = $object->user->id;
            $object->author = $object->user;
        } elseif ($object->isType(PictureType::PICTURE_GEOKRET_MOVE)) {
            $uploader = 'GeoKrety\Controller\MoveAvatarUpload';
            $id = $object->move->id;
            $object->author = $object->move->author;
        } else {
            exit('Should never happen');
        }

        $object->key = $uploader::generateKey($id);
        $object->bucket = $uploader::getBucket();
        $object->uploaded_on_datetime = null;
        $object->update();

        //Creating a presigned URL
        $s3Client = S3Client::instance()->getS3();
        $cmd = $s3Client->getCommand('PutObject', [
            'Bucket' => GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER,
            'Key' => $uploader::fullImgKey($object->key),
        ]);
        $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
        $presignedUrl = (string) $request->getUri();
        $this->_uploadFile($presignedUrl, $fileContent);
    }

    private function _uploadFile($url, $fileContent) {
        $this->trafficWay = "\e[0;31m↑\e[0m";  // ↑↓

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $fileContent);
        $dataLength = ftell($stream);
        rewind($stream);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, [$this, 'progress']);
        curl_setopt($curl, CURLOPT_NOPROGRESS, false); // needed to make progress function work
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, GK_SITE_USER_AGENT);
        curl_setopt($curl, CURLOPT_PUT, true);
        curl_setopt($curl, CURLOPT_INFILE, $stream);
        curl_setopt($curl, CURLOPT_INFILESIZE, $dataLength);
        curl_exec($curl);
        curl_close($curl);
    }

    protected function getConsoleWriterPattern(): string {
        return 'Importing pictures: %6.2f%% (%s/%d) [%s %s %6.2f%%]';
    }

    private function progress($download_size, $downloaded) {
        if ($download_size > 0) {
            $this->transferPercent = $downloaded / $download_size * 100;
        }
        $this->print();
    }

    protected function print(): void {
        $this->console_writer->print([$this->percentProcessed, $this->counter, $this->total, $this->trafficWay, $this->downloadFilename, $this->transferPercent]);
    }
}
