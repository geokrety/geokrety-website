<?php

namespace unit\app\GeoKrety\Emails;

use GeoKrety\Model\User;

class ExpiringAccountsTest extends BaseEmailTestCase {
    protected $tested_class = \GeoKrety\Email\ExpiringAccounts::class;

    public static function emailStatusProvider(): array {
        return [
            [User::USER_EMAIL_NO_ERROR, 0],
            [User::USER_EMAIL_DOES_NOT_EXIST, 0],
            [User::USER_EMAIL_UNCONFIRMED, 1],
            [User::USER_EMAIL_MISSING, 0],
            [User::USER_EMAIL_MAILBOX_FULL, 0],
            [User::USER_EMAIL_DETECTED_AS_SPAM, 0],
        ];
    }
}
