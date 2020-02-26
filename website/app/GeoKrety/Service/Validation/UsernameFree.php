<?php

namespace GeoKrety\Service\Validation;

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
        $user = new User();
        if ($user->count(['username = ? AND (email != ? OR account_valid = ?)', $username, $email, User::USER_ACCOUNT_VALID], null, 0) > 0) {
            array_push($this->errors, sprintf(_('Sorry, but username "%s" is already used.'), $username));
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
