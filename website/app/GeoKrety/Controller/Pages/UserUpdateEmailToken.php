<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\User;
use GeoKrety\Service\UserSettings;
use Sugar\Event;

class UserUpdateEmailToken extends Base {
    public function post(\Base $f3) {
        $user = new User();
        if (!isValidUuid($f3->get('PARAMS.token'))) {
            http_response_code(400);
            echo 'This unsubscribe token is invalid';

            return;
        }

        $user->load(['list_unsubscribe_token = ?', $f3->get('PARAMS.token')]);
        if ($user->dry()) {
            http_response_code(404);
            echo 'This unsubscribe token is invalid';

            return;
        }

        // Unsubscribe from all email notifications using UserSettings
        $userSettings = UserSettings::instance();
        $userSettings->put($user, 'DAILY_DIGEST', 'false');
        $userSettings->put($user, 'INSTANT_NOTIFICATIONS', 'false');
        echo 'Unsubscribed';
        Event::instance()->emit('email.list-unsubscribe.token', $user->list_unsubscribe_token);
    }
}

/**
 * Check if a given string is a valid UUID
 * https://gist.github.com/joel-james/3a6201861f12a7acf4f2.
 *
 * @param mixed $uuid The string to check
 */
function isValidUuid(mixed $uuid): bool {
    return is_string($uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid) === 1;
}
