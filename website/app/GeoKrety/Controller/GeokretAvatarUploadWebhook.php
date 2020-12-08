<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;

class GeokretAvatarUploadWebhook extends Base {
//    /**
//     * @var Picture
//     */
//    private $picture;

    public function post(\Base $f3) {
        if (!$f3->get('HEADERS.Authorization')) {
            http_response_code(400);
            echo 'Missing Header Authorization';
            exit();
        }
        if ($f3->get('HEADERS.Authorization') !== sprintf('Bearer %s', GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED)) {
            http_response_code(400);
            echo 'Wrong Header Authorization';
            exit();
        }

        $s3Data = json_decode($f3->get('BODY'), true)['Records'][0]['s3'];
        // Load picture
        $picture = new Picture();
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
            exit();
        }
        $picture->touch('uploaded_on_datetime');

        if ($picture->validate()) {
            $picture->save();

            \Sugar\Event::instance()->emit('picture.uploaded', $picture);
        }

        $f3->clear('SESSION');
    }
}
