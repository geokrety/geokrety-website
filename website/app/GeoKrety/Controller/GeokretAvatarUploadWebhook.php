<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;

class GeokretAvatarUploadWebhook extends Base {
    public function post(\Base $f3) {
        if (!$f3->get('HEADERS.Authorization')) {
            http_response_code(400);
            echo 'Missing Header Authorization';
            die();
        }
        if ($f3->get('HEADERS.Authorization') !== sprintf('Bearer %s', GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED)) {
            http_response_code(400);
            echo 'Wrong Header Authorization';
            die();
        }

//        // DEBUG
//        $f3->set('LOGS', '/tmp/');
//        $logger = new \Log('error.log');
//        $logger->write(print_r($f3->get('HEADERS'), true));
//        $logger->write(print_r($f3->get('BODY'), true));
//        $logger->write(json_decode($f3->get('BODY'), true)['Key']);

        // Load picture
        $picture = new Picture();
        $s3Data = json_decode($f3->get('BODY'), true)['Records'][0]['s3'];
        $picture->load([
            'key = ? AND bucket = ?',
            $s3Data['object']['key'],
            $s3Data['bucket']['name'],
        ]);
        if ($picture->dry()) {
            http_response_code(404);
            echo 'No such key';
            // TODO: We should remove the uploaded file
            // TODO: Action have to be reported to admins
            die();
        }
        $picture->touch('uploaded_on_datetime');

        if ($picture->validate()) {
            $picture->save();

            \Event::instance()->emit('geokret.avatar.image.uploaded', $picture);
        }
    }
}
