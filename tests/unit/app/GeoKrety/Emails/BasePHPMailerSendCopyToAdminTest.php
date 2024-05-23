<?php

namespace unit\app\GeoKrety\Emails;

use fixtures\UserFixture;
use Mockery;

class BasePHPMailerSendCopyToAdminTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    private $mailer;

    protected function mockeryTestSetUp() {
        parent::mockeryTestSetUp();

        $this->mailer = \Mockery::spy(\GeoKrety\Email\BasePHPMailer::class)->makePartial();
        $this->mailer->allows()->parentSend()->andReturn(true); // Protect sending real mails
        $this->mailer->allows()->isUnitTesting()->andReturn(true);
    }

    public function testNotFoundAdminsAreNotAddedToTheList() {
        $user = UserFixture::getUserFixture();

        $this->mailer->shouldAllowMockingProtectedMethods();
        $this->mailer->shouldReceive('getAdmin')->once()->andReturn(null);

        $this->assertNull($this->mailer->sendCopyToAdmins($user));
        $this->assertCount(0, $this->mailer->recipients);
    }

    public function testAdminsAreAddedToTheList() {
        $admin = UserFixture::getUserFixture(1);
        $user = UserFixture::getUserFixture(2);
        $admin_email = $admin->email;

        $this->mailer->shouldAllowMockingProtectedMethods();
        $this->mailer->shouldReceive('getAdmin')->once()->andReturn($admin);

        $this->assertNull($this->mailer->sendCopyToAdmins($user));
        $this->assertCount(1, $this->mailer->recipients);
        $this->assertEquals($this->mailer->recipients[0]->email, $admin_email);
    }
}
