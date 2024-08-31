<?php

namespace unit\app\GeoKrety\Model;

use GeoKrety\Model\User;
use Mockery;

class UserTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    public function testHasEmailBase() {
        $user = new User();
        $this->assertEquals($user->hasEmail(), false);
    }

    public function testHasEmailHash() {
        $user = new User();
        $user->_email_hash = 'xyz';
        $this->assertEquals($user->hasEmail(), true);
    }

    public function testHasEmailJustSet() {
        $user = new User();
        $user->_email = 'foobar@example.com';
        $this->assertEquals($user->hasEmail(), true);
    }
}
