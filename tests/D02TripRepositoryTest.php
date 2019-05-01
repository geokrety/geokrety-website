<?php

class TripRepositoryTest extends GKTestCase {
    public function setUp() {
        $this->verbose = false;
        parent::setUp();
        $this->assumeTestDatabase();
        $this->testUtil->cleanTestData($this->verbose);
    }

    public function test_trips_geokrety_invalidId() {
        // GIVEN
        $link = GKDB::getLink();
        $tripRepository = new \Geokrety\Repository\TripRepository($link, $this->verbose);
        $geokretyId = 'sdsd';

        // THEN
        $this->expectException(\Exception::class);

        // WHEN
        $tripRepository->getByGeokretyId($geokretyId, 123, $recurs = false);
    }

    public function test_trips_geokrety_not_found() {
        // GIVEN
        $link = GKDB::getLink();
        $tripRepository = new \Geokrety\Repository\TripRepository($link, $this->verbose);
        $geokretyId = 325;

        // WHEN
        $trips = $tripRepository->getByGeokretyId($geokretyId, 123, $recurs = false);

        // THEN
        $this->assertNotNull($trips);
        $this->assertEmpty($trips);
    }

    public function test_trips_success() {
        // GIVEN
        $link = GKDB::getLink();
        $tripRepository = new \Geokrety\Repository\TripRepository($link, $this->verbose);
        $geokretyId = $this->testUtil->givenGeokret(1234567, 'name', '0', 'ABC123');
        $waypointAA = 'GC00AA1';
        $waypointAB = 'GC00AB1';
        $userIdA = $this->testUtil->givenRandomUser('testUserA', 'fr');
        $userIdB = $this->testUtil->givenRandomUser('testUserB', 'pl');
        $this->testUtil->givenWaypoint($waypointAA, rand(40, 75), rand(-15, 35));
        $this->testUtil->givenWaypoint($waypointAB, rand(40, 75), rand(-15, 35));

        $expectedCount = 0;
        $this->testUtil->givenRandomTripData($geokretyId, $userIdA, $waypointAA);
        $this->testUtil->givenRandomTripData($geokretyId, $userIdB, $waypointAB);
        $this->testUtil->givenRandomTripData($geokretyId, $userIdB, $waypointAA);
        $this->testUtil->givenRandomTripData($geokretyId, $userIdA, $waypointAB);
        $expectedCount += 4;

        for ($i = 0; $i < 10; ++$i) {// anonymous exotics logs
            $this->testUtil->givenRandomTripData($geokretyId, 0, 'WPExotic');
        }
        $expectedCount += 10;
        $limit = 100;

        // WHEN
        $trips = $tripRepository->getByGeokretyId($geokretyId, $limit, $recurs = false);

        // THEN
        $this->assertNotNull($trips);
        $this->assertEquals(count($trips), $expectedCount);
        for ($i = 0; $i < 4; ++$i) {
            $this->verifyTrip($trips[$i], true, true);
        }
        for ($i = 4; $i < 14; ++$i) {
            $this->verifyTrip($trips[$i], false, false);
        }
    }

    private function verifyTrip($trip, $verifyUsername, $verifyWaypoint) {
        $this->assertNotNull($trip->lat);
        $this->assertNotNull($trip->lon);
        $this->assertNotNull($trip->alt);
        $this->assertNotNull($trip->ruchId);
        $this->assertNotNull($trip->ruchData);
        $this->assertNotNull($trip->ruchDataDodania);
        $this->assertNotNull($trip->userId);
        $this->assertNotNull($trip->comment);
        $this->assertNotNull($trip->logType);
        $this->assertNotNull($trip->country);
        $this->assertNotNull($trip->distance);
        $this->assertNotNull($trip->waypoint);

        if ($verifyUsername) {
            $this->assertNotNull($trip->username);
        }

        if ($verifyWaypoint) {
            $this->assertNotNull($trip->waypointName);
            $this->assertNotNull($trip->waypointType);
            $this->assertNotNull($trip->waypointOwner);
            $this->assertNotNull($trip->waypointStatus);
            $this->assertNotNull($trip->waypointLink);
        }
    }
}
