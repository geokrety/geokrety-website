<?php

namespace unit\app\GeoKrety\Service\Validation;

require_once __DIR__.'/DatabaseBackedValidationTestCase.php';

use GeoKrety\Model\WaypointGC as WaypointGCModel;
use GeoKrety\Model\WaypointOC as WaypointOCModel;
use GeoKrety\Service\Validation\Waypoint;
use PHPUnit\Framework\Attributes\DataProvider;

class ExposedWaypoint extends Waypoint {
    public function exposedCheckLength($waypoint, $coordinates) {
        return $this->checkLength($waypoint, $coordinates);
    }

    public function exposedCheckCharacters(?string $waypoint) {
        return $this->checkCharacters($waypoint);
    }

    public function exposedCheckIsInDatabase(?string $waypoint, $coordinates) {
        return $this->checkIsInDatabase($waypoint, $coordinates);
    }
}

class WaypointTest extends DatabaseBackedValidationTestCase {
    public static function invalidLengthProvider(): array {
        return [
            'too short' => [str_repeat('A', GK_CHECK_WAYPOINT_MIN_LENGTH - 1)],
            'too long' => [str_repeat('A', GK_CHECK_WAYPOINT_MAX_LENGTH + 1)],
        ];
    }

    public function testCheckLengthAcceptsCoordinatesWithoutWaypoint(): void {
        $validator = new ExposedWaypoint();

        $this->assertTrue($validator->exposedCheckLength('', '57.462633 22.849983'));
        $this->assertSame([], $validator->getErrors());
    }

    public function testCheckLengthRejectsEmptyWaypointWithoutCoordinates(): void {
        $validator = new ExposedWaypoint();

        $this->assertFalse($validator->exposedCheckLength('', null));
        $this->assertSame(['Waypoint seems empty.'], $validator->getErrors());
    }

    #[DataProvider('invalidLengthProvider')]
    public function testCheckLengthRejectsOutOfBoundsWaypoints(string $waypoint): void {
        $validator = new ExposedWaypoint();

        $this->assertFalse($validator->exposedCheckLength($waypoint, null));
        $this->assertSame(
            [sprintf('Waypoint length is invalid. It should be between %d and %d characters long.', GK_CHECK_WAYPOINT_MIN_LENGTH, GK_CHECK_WAYPOINT_MAX_LENGTH)],
            $validator->getErrors()
        );
    }

    public function testCheckCharactersReturnsTrueForNullWaypoint(): void {
        $validator = new ExposedWaypoint();

        $this->assertTrue($validator->exposedCheckCharacters(null));
        $this->assertSame([], $validator->getErrors());
    }

    public function testCheckCharactersRejectsNonAlphanumericInput(): void {
        $validator = new ExposedWaypoint();

        $this->assertFalse($validator->exposedCheckCharacters('GC 12-34'));
        $this->assertSame(['Waypoint contains invalid characters.'], $validator->getErrors());
    }

    public function testCheckIsInDatabaseInitializesGcModelWhenWaypointIsNull(): void {
        $validator = new ExposedWaypoint();

        $this->assertNull($validator->exposedCheckIsInDatabase(null, '57.462633 22.849983'));
        $this->assertInstanceOf(WaypointGCModel::class, $validator->getWaypoint());
    }

    public function testCheckIsInDatabaseLoadsExistingGcWaypoint(): void {
        $this->insertWaypointGc('GCUNIT1', 12.34, 56.78, 'fr');
        $validator = new ExposedWaypoint();

        $this->assertTrue($validator->exposedCheckIsInDatabase('gcunit1', null));
        $this->assertInstanceOf(WaypointGCModel::class, $validator->getWaypoint());
        $this->assertSame('GCUNIT1', $validator->getWaypoint()->waypoint);
    }

    public function testCheckIsInDatabaseLoadsExistingOcWaypoint(): void {
        $this->insertWaypointOc('OCUNIT1', 48.85, 2.35, 'fr');
        $validator = new ExposedWaypoint();

        $this->assertTrue($validator->exposedCheckIsInDatabase('ocunit1', null));
        $this->assertInstanceOf(WaypointOCModel::class, $validator->getWaypoint());
        $this->assertSame('OCUNIT1', $validator->getWaypoint()->waypoint);
    }

    public function testCheckIsInDatabaseRejectsMissingGcWaypointWithoutCoordinates(): void {
        $validator = new ExposedWaypoint();

        $this->assertFalse($validator->exposedCheckIsInDatabase('GCMISS1', null));
        $this->assertSame(
            [
                sprintf('View the <a href="%s" target="_blank">cache page</a>.', sprintf(GK_SERVICE_GO2GEO_URL, 'GCMISS1')),
                'This is a Geocaching.com cache that no one logged yet on GeoKrety.org. To ensure correct travel of this GeoKret, please copy/paste cache coordinates in the "Coordinates" field.',
            ],
            $validator->getErrors()
        );
    }

    public function testCheckIsInDatabaseRejectsMissingOcWaypointWithoutCoordinates(): void {
        $validator = new ExposedWaypoint();

        $this->assertFalse($validator->exposedCheckIsInDatabase('OCMISS1', null));
        $this->assertSame(
            [
                sprintf('View the <a href="%s" target="_blank">cache page</a>.', sprintf(GK_SERVICE_GO2GEO_URL, 'OCMISS1')),
                'Sorry, but this waypoint is not (yet) in our database. Does it really exist?',
            ],
            $validator->getErrors()
        );
    }

    public function testCheckIsInDatabaseAcceptsMissingGcWaypointWithCoordinates(): void {
        $validator = new ExposedWaypoint();

        $this->assertTrue($validator->exposedCheckIsInDatabase('GCMISS2', '57.462633 22.849983'));
        $this->assertSame([], $validator->getErrors());
        $this->assertInstanceOf(WaypointGCModel::class, $validator->getWaypoint());
        $this->assertSame('GCMISS2', $validator->getWaypoint()->waypoint);
        $this->assertSame('57.46263', $validator->getWaypoint()->lat);
        $this->assertSame('22.84998', $validator->getWaypoint()->lon);
    }

    public function testValidateAcceptsMissingWaypointWhenCoordinatesAreProvided(): void {
        $validator = new Waypoint();

        $this->assertTrue($validator->validate('GCMISS4', '57.462633 22.849983'));
        $this->assertInstanceOf(WaypointGCModel::class, $validator->getWaypoint());
        $this->assertSame('57.46263', $validator->getWaypoint()->lat);
        $this->assertSame('22.84998', $validator->getWaypoint()->lon);
    }

    public function testValidateRejectsInvalidCoordinatesOnMissingWaypoint(): void {
        $validator = new Waypoint();

        $this->assertFalse($validator->validate('OCMISS99', 'malformed-coordinates'));
        $this->assertNotEmpty($validator->getErrors());
        // Expect error about invalid format or missing database entry
        $this->assertStringContainsString('waypoint', strtolower(implode(' ', $validator->getErrors())));
    }

    public function testValidateAndRenderExistingWaypoint(): void {
        $this->insertWaypointGc('GCUNIT2', 48.8566, 2.3522, 'fr');
        $validator = new Waypoint();

        $this->assertTrue($validator->validate('gcunit2'));
        $this->assertJsonStringEqualsJsonString(
            '{"waypoint":"GCUNIT2","latitude":"48.85660","longitude":"2.35220","elevation":47,"countryCode":"fr"}',
            $validator->render()
        );
    }

    public function testValidateAndRenderErrorsForMissingWaypoint(): void {
        $validator = new Waypoint();

        $this->assertFalse($validator->validate('GCMISS3'));
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                sprintf('View the <a href="%s" target="_blank">cache page</a>.', sprintf(GK_SERVICE_GO2GEO_URL, 'GCMISS3')),
                'This is a Geocaching.com cache that no one logged yet on GeoKrety.org. To ensure correct travel of this GeoKret, please copy/paste cache coordinates in the "Coordinates" field.',
            ], JSON_UNESCAPED_UNICODE),
            $validator->render()
        );
    }
}
