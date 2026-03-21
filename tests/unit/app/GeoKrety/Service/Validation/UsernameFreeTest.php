<?php

namespace unit\app\GeoKrety\Service\Validation;

require_once __DIR__.'/DatabaseBackedValidationTestCase.php';

use GeoKrety\Model\User;
use GeoKrety\Service\Validation\UsernameFree;

class UsernameFreeTest extends DatabaseBackedValidationTestCase {
    public function testValidateReturnsFalseWhenUsernameIsFree(): void {
        $validator = new UsernameFree();

        $this->assertFalse($validator->validate('available-user'));
        $this->assertSame([], $validator->getErrors());
        $this->assertNull($validator->render());
    }

    public function testValidateReturnsTrueAndErrorWhenUsernameIsAlreadyUsed(): void {
        $this->insertUser('taken-user');
        $validator = new UsernameFree();

        $this->assertTrue($validator->validate('taken-user'));
        $this->assertSame(['Sorry, but username "taken-user" is already used.'], $validator->getErrors());
    }

    public function testValidateReturnsTrueForCaseInsensitiveUsernameCollision(): void {
        $this->insertUser('Taken-User');
        $validator = new UsernameFree();

        $this->assertTrue($validator->validate('taken-user'));
        $this->assertSame(['Sorry, but username "taken-user" is already used.'], $validator->getErrors());
    }

    public function testValidateNormalizesUnicodeWhitespaceBeforeLookup(): void {
        $this->insertUser('Space Name');
        $validator = new UsernameFree();

        $this->assertTrue($validator->validate("Space\u{00A0}\tName"));
        $this->assertSame(['Sorry, but username "Space Name" is already used.'], $validator->getErrors());
    }

    public function testValidateIgnoresMatchingPendingEmailWhenEmailArgumentProvided(): void {
        $this->insertUser('pending-user', 'pending@example.com', User::USER_ACCOUNT_NON_ACTIVATED);
        $validator = new UsernameFree();

        $this->assertFalse($validator->validate('different-user', 'pending@example.com'));
        $this->assertSame([], $validator->getErrors());
    }

    public function testValidateReturnsTrueWhenUsernameMatchesExistingEmailHashWithoutEmailArgument(): void {
        $this->insertUser('existing-user', 'hash-match@example.com');
        $validator = new UsernameFree();

        $this->assertTrue($validator->validate('hash-match@example.com'));
        $this->assertSame(['Sorry, but username "hash-match@example.com" is already used.'], $validator->getErrors());
    }

    public function testValidateReturnsLoginErrorWhenDuplicateExistsWithEmailArgument(): void {
        $this->insertUser('taken-email-user', 'used@example.com');
        $validator = new UsernameFree();
        $expected = sprintf(
            'Sorry, but username "taken-email-user" is already used. If that\'s your account, please <a href="%s">login</a> first.',
            \Base::instance()->alias('login')
        );

        $this->assertTrue($validator->validate('taken-email-user', 'other@example.com'));
        $this->assertSame([$expected], $validator->getErrors());
        $this->assertSame($expected, json_decode($validator->render(), true, 512, JSON_THROW_ON_ERROR));
    }
}
