<?php

namespace unit\app\GeoKrety\Service;

use Mockery;
use PHPCoord\CoordinateReferenceSystem\Geographic2D;
use PHPCoord\Point\GeographicPoint;
use PHPCoord\UnitOfMeasure\Angle\Degree;

// See https://phpcoord.net/en/stable/builtin_units_angles.html

class CoordinateConverterTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    public function testCheckActualConverter() {
        $cases_valid = [
            ['test' => '57.462633 22.849983', 'result' => ['lat' => 57.462633, 'lon' => 22.849983, 'format' => 'fromDecimalDegree']],
            ['test' => '52.796760 -8.442179', 'result' => ['lat' => 52.796760, 'lon' => -8.442179, 'format' => 'fromDecimalDegree']],
            ['test' => 'N 57° 27.758 E 022° 50.999', 'result' => ['lat' => 57.462633, 'lon' => 22.849983, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => "N 57° 27.758' E 022° 50.999'", 'result' => ['lat' => 57.462633, 'lon' => 22.849983, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => "N 057° 27,758' E 022° 50,999'", 'result' => ['lat' => 57.462633, 'lon' => 22.849983, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => "N 57° 27' E 022° 50'", 'result' => ['lat' => 57.450000, 'lon' => 22.833333, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => '32T E 327620 N 4840069', 'result' => ['lat' => 43.693627, 'lon' => 6.860929, 'format' => 'UTM']],
            ['test' => "S 05° 59.349' E 039° 22.677'", 'result' => ['lat' => -5.989150, 'lon' => 39.377950, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => 'S 05° 59.349 E 039° 22.677', 'result' => ['lat' => -5.989150, 'lon' => 39.377950, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => '37M E 541828 N 9337980', 'result' => ['lat' => -5.989154, 'lon' => 39.377944, 'format' => 'UTM']],
            ['test' => 'N52°47.862 O008° 23.786', 'result' => ['lat' => 52.797700, 'lon' => -8.396433, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => "N52°47.862' O008° 23.786'", 'result' => ['lat' => 52.797700, 'lon' => -8.396433, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => 'N52°47.862 O008°.23.786', 'result' => ['lat' => 52.797700, 'lon' => -8.396433, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => 'N52°.47.862 O008°,23.786', 'result' => ['lat' => 52.797700, 'lon' => -8.396433, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => 'N 52° 47.806 W 008° 26.530', 'result' => ['lat' => 52.796767, 'lon' => -8.442167, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => '29U E 537611 N 5849808', 'result' => ['lat' => 52.796760, 'lon' => -8.442179, 'format' => 'UTM']],
            ['test' => "N 52° 47.806' W 008° 26.530'", 'result' => ['lat' => 52.796767, 'lon' => -8.442167, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => '50.260147°N 21.970291°E', 'result' => ['lat' => 50.260147, 'lon' => 21.970291, 'format' => 'fromDegreeHemisphere']],
            ['test' => '50.260147°N, 21.970291°E', 'result' => ['lat' => 50.260147, 'lon' => 21.970291, 'format' => 'fromDegreeHemisphere']],
            ['test' => '56.326N 54.235W', 'result' => ['lat' => 56.326, 'lon' => -54.235, 'format' => 'fromDegreeHemisphere']],
            ['test' => '56.326N 54.235E', 'result' => ['lat' => 56.326, 'lon' => 54.235, 'format' => 'fromDegreeHemisphere']],
            ['test' => 'N56.326 E54.235', 'result' => ['lat' => 56.326, 'lon' => 54.235, 'format' => 'fromHemisphereDegree']],
            ['test' => '50°54′N 15°44′E', 'result' => ['lat' => 50.9, 'lon' => 15.733333, 'format' => 'fromDegreeMinuteHemisphere']],
            ['test' => "53°50'16.85\"N 23° 6'19.78\"E", 'result' => ['lat' => 53.838014, 'lon' => 23.105494, 'format' => 'fromDegreeMinuteSecondHemisphere']],
            ['test' => "49°25'59.146\"N, 17°28'25.660\"E", 'result' => ['lat' => 49.433096, 'lon' => 17.473794, 'format' => 'fromDegreeMinuteSecondHemisphere']],
            ['test' => '46° 44′ 0″ N, 12° 11′ 0″ E', 'result' => ['lat' => 46.733333, 'lon' => 12.183333, 'format' => 'fromDegreeMinuteSecondHemisphere']],
            ['test' => '55° 3′ 23.96″ N, 9° 44′ 32.71″ E', 'result' => ['lat' => 55.056656, 'lon' => 9.742419, 'format' => 'fromDegreeMinuteSecondHemisphere']],
            ['test' => 'N55° 3′ 23.96″ E9° 44′ 32.71″', 'result' => ['lat' => 55.056656, 'lon' => 9.742419, 'format' => 'fromHemisphereDegreeMinuteSecond']],
            ['test' => 'N 53° 28,704\' · E 014° 47,064\'', 'result' => ['lat' => 53.478400, 'lon' => 14.784400, 'format' => 'fromHemisphereDegreeMinute']],

            // Examples from the help page
            ['test' => '52.205205 21.190891', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromDecimalDegree']],
            ['test' => '52.205205/21.190891', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromDecimalDegree']],
            ['test' => '52.205205\\21.190891', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromDecimalDegree']],
            ['test' => '52.205205,21.190891', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromDecimalDegree']],
            ['test' => 'N 52.205205 E 21.190891', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromHemisphereDegree']],
            ['test' => 'N 52.205205 W 21.190891', 'result' => ['lat' => 52.205205, 'lon' => -21.190891, 'format' => 'fromHemisphereDegree']],
            ['test' => '+52.205205 -21.190891', 'result' => ['lat' => 52.205205, 'lon' => -21.190891, 'format' => 'fromDecimalDegree']],

            ['test' => '52 12.3123 21 11.45345', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => '52 12.3123 -21 11.45345', 'result' => ['lat' => 52.205205, 'lon' => -21.190891, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => '+52 12.3123 -21 11.45345', 'result' => ['lat' => 52.205205, 'lon' => -21.190891, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => '+52 12.3123 +21 11.45345', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => 'N52 12.3123 W21 11.45345', 'result' => ['lat' => 52.205205, 'lon' => -21.190891, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => "N 52° 10.369' W 021° 01.542'", 'result' => ['lat' => 52.172817, 'lon' => -21.0257, 'format' => 'fromHemisphereDegreeMinute']],

            ['test' => '52 12 18.74 -21 11 27.21', 'result' => ['lat' => 52.205206, 'lon' => -21.190892, 'format' => 'fromHemisphereDegreeMinuteSecond']],
            ['test' => "N52° 12' 18.74\", W21° 11' 27.21\"", 'result' => ['lat' => 52.205206, 'lon' => -21.190892, 'format' => 'fromHemisphereDegreeMinuteSecond']],
            ['test' => "52° 12' 18.74\" -21° 11' 27.21\"", 'result' => ['lat' => 52.205206, 'lon' => -21.190892, 'format' => 'fromHemisphereDegreeMinuteSecond']],
            ['test' => "52° 12' 18.74\", -21° 11' 27.21\"", 'result' => ['lat' => 52.205206, 'lon' => -21.190892, 'format' => 'fromDegreeMinuteSecond']],

            // Examples from legacy code
            ['test' => "N 52° 12' 18.74\", E 21° 11' 27.21\"", 'result' => ['lat' => 52.205206, 'lon' => 21.190892, 'format' => 'fromHemisphereDegreeMinuteSecond']],
            ['test' => "N 52° 12' 18\", E 21° 11' 27\"", 'result' => ['lat' => 52.205000, 'lon' => 21.190833, 'format' => 'fromHemisphereDegreeMinuteSecond']],
            ['test' => 'N52 12.3123 E21 11.45345', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => 'N 52 12.3123 E 21 11.45345', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => "N 52° 10.369' - 021° 01.542'", 'result' => ['lat' => 52.172817, 'lon' => -21.025700, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => 'S 52°36.002 E013°19.205', 'result' => ['lat' => -52.600033, 'lon' => 13.320083, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => "S 52°36.002' E013°19.205'", 'result' => ['lat' => -52.600033, 'lon' => 13.320083, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => 'N 49°49.59 W 09°52.2', 'result' => ['lat' => 49.826500, 'lon' => -9.870000, 'format' => 'fromHemisphereDegreeMinute']],
            ['test' => '- 49°49`59.282" E 09°52´21.216"', 'result' => ['lat' => -49.833134, 'lon' => 9.872560, 'format' => 'fromHemisphereDegreeMinuteSecond']],
            ['test' => '52.205205 21.190891', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromDecimalDegree']],
            ['test' => '52.205205/21.190891', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromDecimalDegree']],
            ['test' => '52.205205\\21.190891', 'result' => ['lat' => 52.205205, 'lon' => 21.190891, 'format' => 'fromDecimalDegree']],
            ['test' => 'N 52.205205 W 21.190891', 'result' => ['lat' => 52.205205, 'lon' => -21.190891, 'format' => 'fromHemisphereDegree']],
            ['test' => '-52.205205 +21.190891', 'result' => ['lat' => -52.205205, 'lon' => 21.190891, 'format' => 'fromDecimalDegree']],

            // New cases
            ['test' => 'N 47° 13.624\' E 5° 34.542\'', 'result' => ['lat' => 47.227067, 'lon' => 5.575700, 'format' => 'fromHemisphereDegreeMinute']],
        ];
        $cases_error = [
            'North.: 6189860 East.: 544201',
        ];

        foreach ($cases_error as $case) {
            $res = \GeoKrety\Service\CoordinatesConverter::parse($case);
            $this->assertEquals('', $res[0]);
            $this->assertEquals('', $res[1]);
            $this->assertEquals('', $res['format']);
            $this->assertEquals('Bad coordinates or unknown format.', $res['error']);
            // print("TEST: " . $case . PHP_EOL);
            // print_r($res);
        }

        foreach ($cases_valid as $case) {
            $res = \GeoKrety\Service\CoordinatesConverter::parse($case['test']);
            // print("TEST: " . $case["test"] . PHP_EOL);
            // print_r($res);

            $this->assertEquals($case['result']['lat'], $res[0]);
            $this->assertEquals($case['result']['lon'], $res[1]);
            $this->assertEquals($case['result']['format'], $res['format']);
        }
    }

    public function testPHPCoord() {
        // the Statue of Liberty in WGS84 (unknown date), traditional arguments, decimal degrees
        $crs = Geographic2D::fromSRID(Geographic2D::EPSG_WGS_84);
        $point = GeographicPoint::create(
            $crs,
            new Degree(40.689167),
            new Degree(-74.044444),
            null
        );
        $this->assertEquals(40.689167, $point->getLatitude()->getValue());
        $this->assertEquals(-74.044444, $point->getLongitude()->getValue());
    }

    public function testParseFromDegreeMinuteSecondHemisphere() {
        // N 57° 27.758 E 022° 50.999
        $crs = Geographic2D::fromSRID(Geographic2D::EPSG_WGS_84);
        $point = GeographicPoint::create(
            $crs,
            Degree::fromDegreeMinuteSecondHemisphere('40° 41′ 21″ N'),
            Degree::fromDegreeMinuteSecondHemisphere('74° 2′ 40″ W'),
            null
        );
        // print_r($point->getLatitude());
        $this->assertEquals(40.689166666666665, $point->getLatitude()->getValue());
        $this->assertEquals(-74.04444444444444, $point->getLongitude()->getValue());
    }
}
