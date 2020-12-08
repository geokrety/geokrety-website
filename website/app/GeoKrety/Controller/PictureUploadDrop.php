<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Picture;
use function Sentry\captureMessage;

class PictureUploadDrop extends Base {
    public function drop_s3_file_signature(\Base $f3) {
        if ($f3->get('HEADERS.Host') !== 'website' || $f3->get('HEADERS.Authorization') !== sprintf('Bearer %s', GK_AUTH_TOKEN_DROP_S3_FILE_UPLOAD_REQUEST)) {
            captureMessage('GeokretAvatarUploadDrop: Unauthorized access attempt');

            return;
        }

        // Load picture
        $key = $f3->get('PARAMS.key');
        $picture = new Picture();
        $picture->load(['key = ? AND uploaded_on_datetime = ?', $key, null]);
        if ($picture->dry()) {
            captureMessage('GeokretAvatarUploadDrop: No such Picture in db');
            http_response_code(404);
            exit();
        }

        captureMessage('GeokretAvatarUploadDrop: Picture erased');
        $picture->erase();
    }
}
