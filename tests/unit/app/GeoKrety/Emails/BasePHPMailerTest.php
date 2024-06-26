<?php

namespace unit\app\GeoKrety\Emails;

use fixtures\UserFixture;
use Mockery;

class BasePHPMailerTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    private $mailer;

    protected function mockeryTestSetUp() {
        parent::mockeryTestSetUp();

        $this->mailer = \Mockery::spy(\GeoKrety\Email\BasePHPMailer::class)->makePartial();
        $this->mailer->allows()->parentSend()->andReturn(true); // Protect sending real mails
        $this->mailer->allows()->sendCopyToAdmins()->andReturns();
        $this->mailer->allows()->isUnitTesting()->andReturn(false);
    }

    public function testNoMailSentByDefault() {
        $user = UserFixture::getUserFixture();

        $this->assertNull($this->mailer->setTo($user));
        $this->assertCount(0, $this->mailer->recipients);

        $this->mailer->shouldAllowMockingProtectedMethods();

        $this->assertNull($this->mailer->setTo($user));
        $this->assertCount(0, $this->mailer->recipients);

        $this->mailer->shouldAllowMockingProtectedMethods();
        $this->mailer->allows()->isProduction()->andReturn(true);

        $this->assertNull($this->mailer->setTo($user));
        $this->assertCount(1, $this->mailer->recipients);
    }
}
