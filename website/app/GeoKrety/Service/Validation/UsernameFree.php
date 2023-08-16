<?php

namespace GeoKrety\Service\Validation;

use GeoKrety\Model\User;

class UsernameFree {
    private array $errors = [];
    private $username;

    public function getUsername() {
        return $this->username;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function validate($username, $email = null) {
        $this->lookupUsername($username, $email);

        return true;
    }

    private function lookupUsername($username, $email) {
        $f3 = \Base::instance();
        $user = new User();
        $username = trim(preg_replace('/(\pZ\pC)+/u', ' ', $username));
        if ($user->count(['lower(username) = lower(?) OR _email_hash = public.digest(lower(?), \'sha256\')', $username, $username], ttl: 0) > 0) {
            if (is_null($email)) {
                array_push($this->errors, sprintf(_('Sorry, but username "%s" is already used.'), $username));
            } else {
                array_push($this->errors, sprintf(_('Sorry, but username "%s" is already used.').' '._('If that\'s your account, please <a href="%s">login</a> first.'), $username, $f3->alias('login')));
            }
        }
    }

    public function render() {
        header('Content-Type: application/json; charset=utf-8');
        if (sizeof($this->errors)) {
            http_response_code(400);

            return json_encode(implode(' ; ', $this->errors), JSON_UNESCAPED_UNICODE);
        }
    }
}
