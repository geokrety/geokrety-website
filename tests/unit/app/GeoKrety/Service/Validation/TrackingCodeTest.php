<?php

namespace unit\app\GeoKrety\Service\Validation;

require_once __DIR__.'/DatabaseBackedValidationTestCase.php';

use GeoKrety\Service\Validation\TrackingCode;

class ExposedTrackingCode extends TrackingCode {
    public function exposedCheckCharacters($trackingCode) {
        return $this->checkCharacters($trackingCode);
    }
}

class TrackingCodeTest extends DatabaseBackedValidationTestCase {
    public function testSplitTrackingCodesNormalizesDeduplicatesAndDropsEmptyValues(): void {
        $this->assertSame(
            ['ABCD12', 'EFGH34'],
            TrackingCode::split_tracking_codes('  abcd12, EFGH34, abcd12 ,, ')
        );
        $this->assertSame([], TrackingCode::split_tracking_codes(null));
    }

    public function testProtectedCheckCharactersRejectsInvalidCharacters(): void {
        $validator = new ExposedTrackingCode();

        $this->assertFalse($validator->exposedCheckCharacters('BAD-CODE'));
        $this->assertSame(['Tracking Code "BAD-CODE" contains invalid characters.'], $validator->getErrors());
    }

    public function testValidateRejectsMissingInput(): void {
        $validator = new TrackingCode();

        $this->assertFalse($validator->validate(null));
        $this->assertSame(['No Tracking Code provided.'], $validator->getErrors());
    }

    public function testValidateRejectsMultipleCodesForAnonymousUsers(): void {
        $validator = new TrackingCode();

        $this->assertFalse($validator->validate('ABCD12, EFGH34'));
        $this->assertSame(
            ['Anonymous users cannot check multiple Tracking Codes at once. Please login first.'],
            $validator->getErrors()
        );
    }

    public function testValidateRejectsTooManyCodes(): void {
        \Base::instance()->set('SESSION.CURRENT_USER', $this->insertUser('validator-user'));
        $validator = new TrackingCode();
        $codes = [];

        for ($i = 0; $i < GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS + 1; ++$i) {
            $codes[] = sprintf('CODE%02d', $i);
        }

        $this->assertFalse($validator->validate(implode(',', $codes)));
        $this->assertSame(
            [sprintf('Only %d Tracking Codes may be specified at once, there are %d selected.', GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS, GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS + 1)],
            $validator->getErrors()
        );
    }

    public function testValidateRejectsPublicGkIdentifierWhenTrackingCodeIsNotFound(): void {
        $validator = new TrackingCode();

        $this->assertFalse($validator->validate('GKABCD'));
        $this->assertSame(
            ['You seems to have used the GeoKret public identifier "GKABCD". We need the private code (Tracking Code) here. Hint: it doesn\'t start with \'GK\' 😉'],
            $validator->getErrors()
        );
    }

    public function testValidateRejectsTooShortTrackingCode(): void {
        $validator = new TrackingCode();
        $code = str_repeat('A', GK_SITE_TRACKING_CODE_MIN_LENGTH - 1);

        $this->assertFalse($validator->validate($code));
        $this->assertSame(
            [sprintf('Tracking Code "%s" seems too short. We expect at least %d characters here.', $code, GK_SITE_TRACKING_CODE_MIN_LENGTH)],
            $validator->getErrors()
        );
    }

    public function testValidateRejectsTooLongTrackingCode(): void {
        $validator = new TrackingCode();
        $code = str_repeat('A', GK_SITE_TRACKING_CODE_MAX_LENGTH + 1);

        $this->assertFalse($validator->validate($code));
        $this->assertSame(
            [sprintf('Tracking Code "%s" seems too long. We expect %d characters maximum here.', $code, GK_SITE_TRACKING_CODE_MAX_LENGTH)],
            $validator->getErrors()
        );
    }

    public function testValidateRejectsUnknownTrackingCode(): void {
        $validator = new TrackingCode();

        $this->assertFalse($validator->validate('ABCD12'));
        $this->assertSame(
            ['Sorry, but Tracking Code "ABCD12" was not found in our database.'],
            $validator->getErrors()
        );
    }

    public function testValidateAcceptsRealTrackingCodeThatStartsWithGk(): void {
        $this->db()->exec('ALTER TABLE gk_geokrety DISABLE TRIGGER before_20_manage_tracking_code');
        $this->insertGeokret('GK1234', 'GK-prefixed tracking code');
        $this->db()->exec('ALTER TABLE gk_geokrety ENABLE TRIGGER before_20_manage_tracking_code');
        $validator = new TrackingCode();

        $this->assertTrue($validator->validate('gk1234'));
        $this->assertCount(1, $validator->getGeokrety());
    }

    public function testValidateLoadsMatchingGeokretAndRenderOutputsPayload(): void {
        $id = $this->insertGeokret('ABCD12', 'Unit Test GeoKret');
        \GeoKrety\Service\Smarty::getSmarty()->setTemplateDir([
            'main' => __DIR__.'/../../../../../../website/app-templates/smarty',
        ]);
        $validator = new TrackingCode();

        $this->assertTrue($validator->validate('abcd12'));

        $response = json_decode($validator->render(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(1, $response);
        $this->assertSame($id, $response[0]['id']);
        $this->assertSame(sprintf('GK%04X', $id), $response[0]['gkid']);
        $this->assertSame('ABCD12', $response[0]['nr']);
        $this->assertSame('Unit Test GeoKret', $response[0]['name']);
        $this->assertSame('Mission text', $response[0]['mission']);
        $this->assertSame('2024-01-02T03:04:05+00:00', $response[0]['createdOnDatetime']);
        $this->assertArrayHasKey('bornOnDatetime', $response[0]);
        $this->assertIsString($response[0]['bornOnDatetime']);
        $this->assertSame(0, $response[0]['type']);
        $this->assertSame('Traditional', $response[0]['typeString']);
        $this->assertFalse($response[0]['missing']);
        $this->assertTrue($response[0]['collectible']);
        $this->assertArrayHasKey('html', $response[0]);
    }

    public function testValidateAllowsMultipleCodesForLoggedInUsers(): void {
        $userId = $this->insertUser('logged-in-user');
        $this->insertGeokret('ABCD12', 'First');
        $this->insertGeokret('EFGH34', 'Second');
        \Base::instance()->set('SESSION.CURRENT_USER', $userId);

        $validator = new TrackingCode();

        $this->assertTrue($validator->validate('abcd12, efgh34'));
        $this->assertCount(2, $validator->getGeokrety());
    }

    public function testValidateContinuesCollectingValidCodesWhenOneEntryFails(): void {
        $userId = $this->insertUser('mixed-batch-user');
        $this->insertGeokret('ABCD12', 'First');
        \Base::instance()->set('SESSION.CURRENT_USER', $userId);

        $validator = new TrackingCode();

        $this->assertFalse($validator->validate('abcd12, unknown'));
        $this->assertCount(1, $validator->getGeokrety());
        $this->assertSame(['Sorry, but Tracking Code "UNKNOWN" was not found in our database.'], $validator->getErrors());
    }

    public function testRenderReturnsJsonErrors(): void {
        $validator = new TrackingCode();
        $validator->validate('BAD-CODE');

        $this->assertJsonStringEqualsJsonString(
            '["Tracking Code \"BAD-CODE\" contains invalid characters."]',
            $validator->render()
        );
    }

    public function testValidatePayloadStructureIsDeterministic(): void {
        $userId = $this->insertUser('payload-user');
        $this->insertGeokret('PAYLOAD', 'Payload Test GeoKret');
        \Base::instance()->set('SESSION.CURRENT_USER', $userId);

        $validator = new TrackingCode();
        $this->assertTrue($validator->validate('PAYLOAD'));

        // Decode and validate structure independently of Smarty rendering
        $response = json_decode($validator->render(), true);
        $this->assertIsArray($response);
        $this->assertCount(1, $response);

        $payload = $response[0];
        // Validate required fields exist with correct types
        $this->assertIsInt($payload['id'] ?? null);
        $this->assertIsString($payload['gkid'] ?? null);
        $this->assertIsString($payload['nr'] ?? null);
        $this->assertIsString($payload['name'] ?? null);
        $this->assertIsString($payload['mission'] ?? null);
        $this->assertIsString($payload['createdOnDatetime'] ?? null);
        $this->assertIsString($payload['bornOnDatetime'] ?? null);
        $this->assertIsInt($payload['type'] ?? null);
        $this->assertIsString($payload['typeString'] ?? null);
        $this->assertIsBool($payload['missing'] ?? null);
        $this->assertIsBool($payload['collectible'] ?? null);
        // Check that HTML field exists (always rendered, structure varies)
        $this->assertArrayHasKey('html', $payload);
    }
}
