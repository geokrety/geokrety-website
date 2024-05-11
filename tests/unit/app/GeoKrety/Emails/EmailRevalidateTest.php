<?php

namespace unit\app\GeoKrety\Emails;

use fixtures\UserFixture;
use GeoKrety\Model\User;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

class EmailRevalidateTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    private $mailer;
    protected $tested_class = \GeoKrety\Email\EmailRevalidate::class;

    public static function emailStatusProvider(): array {
        return [
            [User::USER_ACCOUNT_NON_ACTIVATED, User::USER_EMAIL_NO_ERROR, 0],
            [User::USER_ACCOUNT_NON_ACTIVATED, User::USER_EMAIL_DOES_NOT_EXIST, 0],
            [User::USER_ACCOUNT_NON_ACTIVATED, User::USER_EMAIL_UNCONFIRMED, 0],
            [User::USER_ACCOUNT_NON_ACTIVATED, User::USER_EMAIL_MISSING, 0],
            [User::USER_ACCOUNT_NON_ACTIVATED, User::USER_EMAIL_MAILBOX_FULL, 0],
            [User::USER_ACCOUNT_NON_ACTIVATED, User::USER_EMAIL_DETECTED_AS_SPAM, 0],

            [User::USER_ACCOUNT_ACTIVATED, User::USER_EMAIL_NO_ERROR, 0],
            [User::USER_ACCOUNT_ACTIVATED, User::USER_EMAIL_DOES_NOT_EXIST, 0],
            [User::USER_ACCOUNT_ACTIVATED, User::USER_EMAIL_UNCONFIRMED, 0],
            [User::USER_ACCOUNT_ACTIVATED, User::USER_EMAIL_MISSING, 0],
            [User::USER_ACCOUNT_ACTIVATED, User::USER_EMAIL_MAILBOX_FULL, 0],
            [User::USER_ACCOUNT_ACTIVATED, User::USER_EMAIL_DETECTED_AS_SPAM, 0],

            [User::USER_ACCOUNT_IMPORTED, User::USER_EMAIL_NO_ERROR, 1],
            [User::USER_ACCOUNT_IMPORTED, User::USER_EMAIL_DOES_NOT_EXIST, 0],
            [User::USER_ACCOUNT_IMPORTED, User::USER_EMAIL_UNCONFIRMED, 1],
            [User::USER_ACCOUNT_IMPORTED, User::USER_EMAIL_MISSING, 0],
            [User::USER_ACCOUNT_IMPORTED, User::USER_EMAIL_MAILBOX_FULL, 0],
            [User::USER_ACCOUNT_IMPORTED, User::USER_EMAIL_DETECTED_AS_SPAM, 0],
        ];
    }

    public static function emailStatusProviderNonProd(): array {
        return self::emailStatusProvider();
    }

    #[DataProvider('emailStatusProvider')]
    public function testEmailStatuses(int $accountStatus, int $emailStatus, int $expected) {
        $this->mailer->shouldAllowMockingProtectedMethods();
        $this->mailer->allows()->isProduction()->andReturn(true);
        $this->runTests($accountStatus, $emailStatus, $expected);
    }

    #[DataProvider('emailStatusProviderNonProd')]
    public function testEmailStatusesNonProd(int $accountStatus, int $emailStatus, int $expected) {
        $this->mailer->shouldAllowMockingProtectedMethods();
        $this->mailer->allows()->isProduction()->andReturn(false);
        $this->runTests($accountStatus, $emailStatus, $expected);
    }

    protected function mockeryTestSetUp() {
        parent::mockeryTestSetUp();

        $this->mailer = \Mockery::spy($this->tested_class)->makePartial();
        $this->mailer->allows()->parentSend()->andReturn(true); // Protect sending real mails
        $this->mailer->allows()->sendCopyToAdmins()->andReturns();
    }

    private function runTests(int $accountStatus, int $emailStatus, int $expected): void {
        $this->mailer->recipients = [];

        $user = UserFixture::getUser1Fixture();
        $user->account_valid = $accountStatus;
        $user->email_invalid = $emailStatus;

        $this->assertNull($this->mailer->setTo($user));
        $this->assertEquals($expected, sizeof($this->mailer->recipients));
    }
}
