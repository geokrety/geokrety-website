<?php

namespace fixtures;

use GeoKrety\Model\User;

class UserFixture extends \PHPUnit\Framework\Assert {
    public static function getUser1Fixture() {
        $user = \Mockery::spy(new User());
        $user->username = 'username 1';
        $user->email = 'test1@geokrety.org';
        $user->_email_hash = hash('sha256', 'test1@geokrety.org');
        $user->email_invalid = User::USER_EMAIL_NO_ERROR;
        $user->account_valid = User::USER_ACCOUNT_ACTIVATED;

        self::assertTrue($user->hasEmail(), 'testing user hasEmail() mock');

        return $user;
    }
}
