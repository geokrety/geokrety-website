<?php

namespace unit\app\GeoKrety\Service\Validation;

use GeoKrety\Service\Validation\Coordinates;
use Mockery;

class CoordinatesTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    public function testValidateReturnsCoordinatesAndRenderOutputsJson(): void {
        $validator = new Coordinates();

        $this->assertTrue($validator->validate('57.462633 22.849983'));
        $this->assertSame([], $validator->getErrors());
        $this->assertEquals(
            ['lat' => 57.46263, 'lon' => 22.84998, 'format' => 'fromDecimalDegree'],
            $validator->getCoordinates()
        );
        $this->assertJsonStringEqualsJsonString(
            '{"lat":57.46263,"lon":22.84998,"format":"fromDecimalDegree"}',
            $validator->render()
        );
    }

    public function testValidateReturnsMissingCoordinatesErrorForEmptyInput(): void {
        $validator = new Coordinates();

        $this->assertFalse($validator->validate(''));
        $this->assertSame(['Missing or invalid coordinates.'], $validator->getErrors());
        $this->assertJsonStringEqualsJsonString(
            '["Missing or invalid coordinates."]',
            $validator->render()
        );
    }

    public function testValidateReturnsParsingErrorForUnknownFormat(): void {
        $validator = new Coordinates();

        $this->assertFalse($validator->validate('North.: 6189860 East.: 544201'));
        $this->assertSame(['Bad coordinates or unknown format.'], $validator->getErrors());
        $this->assertJsonStringEqualsJsonString(
            '["Bad coordinates or unknown format."]',
            $validator->render()
        );
    }
}
