<?php

namespace unit\app\GeoKrety\Emails;

use GeoKrety\Model\User;

class EmailChangeTest extends BaseEmailTestCase {
    protected $tested_class = \GeoKrety\Email\EmailChange::class;

    public static function emailStatusProvider(): array {
        return [
            [User::USER_EMAIL_NO_ERROR, 1],
            [User::USER_EMAIL_DOES_NOT_EXIST, 1],
            [User::USER_EMAIL_UNCONFIRMED, 1],
            [User::USER_EMAIL_MISSING, 1],
            [User::USER_EMAIL_MAILBOX_FULL, 1],
            [User::USER_EMAIL_DETECTED_AS_SPAM, 1],
        ];
    }

    public static function emailStatusProviderNonProd(): array {
        return self::emailStatusProvider();
    }
}
