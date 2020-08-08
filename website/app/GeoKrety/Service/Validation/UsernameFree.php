<?php

namespace GeoKrety\Service\Validation;

use Base;
use GeoKrety\Model\User;

class UsernameFree {
    private $errors = [];
    private $username = null;

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
        $f3 = Base::instance();
        $user = new User();
        if ($user->count(['lower(username) = lower(?) OR _email_hash = public.digest(lower(?), \'sha256\')', $username, $email], null, 0) > 0) {
            array_push($this->errors, sprintf(_('Sorry, but username "%s" is already used. If that\'s your account, please <a href="%s">login</a> first.'), $username, $f3->alias('login')));
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
