<?php

namespace GeoKrety\Controller;

use CurrentUserLoader;

class UserSettings extends Base {
    use CurrentUserLoader;

    public function post(\Base $f3) {
        if (!$f3->exists('POST')) {
            return;
        }

        $errors = [];
        foreach ($f3->get('POST') as $setting_name => $setting_value) {
            if (\GeoKrety\Service\UserSettings::putForCurrentUser($setting_name, $setting_value) === false) {
                $errors[] = sprintf(_('Failed to save setting "%s"'), $setting_name);
            }
        }

        if (sizeof($errors)) {
            $f3->status(400);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(sizeof($errors) ? $errors : null);
    }
}
