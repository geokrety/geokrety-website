<?php

namespace unit\app\GeoKrety\Emails;

use fixtures\UserFixture;
use GeoKrety\Model\User;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class BaseEmailTestCase extends Mockery\Adapter\Phpunit\MockeryTestCase {
    protected $mailer;
    protected $tested_class = \GeoKrety\Email\AccountActivation::class;

    abstract public static function emailStatusProvider(): array;

    public static function emailStatusProviderNonProd(): array {
        return [
            [User::USER_EMAIL_NO_ERROR, 0],
            [User::USER_EMAIL_DOES_NOT_EXIST, 0],
            [User::USER_EMAIL_UNCONFIRMED, 0],
            [User::USER_EMAIL_MISSING, 0],
            [User::USER_EMAIL_MAILBOX_FULL, 0],
            [User::USER_EMAIL_DETECTED_AS_SPAM, 0],
        ];
    }

    #[DataProvider('emailStatusProvider')]
    public function testEmailStatuses(int $emailStatus, int $expected) {
        $this->mailer->shouldAllowMockingProtectedMethods();
        $this->mailer->allows()->isProduction()->andReturn(true);
        $this->runTests($emailStatus, $expected);
    }

    #[DataProvider('emailStatusProviderNonProd')]
    public function testEmailStatusesNonProd(int $emailStatus, int $expected) {
        $this->mailer->shouldAllowMockingProtectedMethods();
        $this->mailer->allows()->isProduction()->andReturn(false);
        $this->runTests($emailStatus, $expected);
    }

    protected function mockeryTestSetUp() {
        parent::mockeryTestSetUp();

        $this->mailer = \Mockery::spy($this->tested_class)->makePartial();
        $this->mailer->allows()->parentSend()->andReturn(true); // Protect sending real mails
        $this->mailer->allows()->sendCopyToAdmins()->andReturns();
        $this->mailer->allows()->isUnitTesting()->andReturn(false);
    }

    private function runTests(int $emailStatus, int $expected): void {
        $this->mailer->recipients = [];

        $user = UserFixture::getUserFixture();
        $user->email_invalid = $emailStatus;

        $this->assertNull($this->mailer->setTo($user));
        $this->assertEquals($expected, sizeof($this->mailer->recipients));
    }
}
