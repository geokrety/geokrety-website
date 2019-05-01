<?php

class TripServiceTest extends GKTestCase {
    public function setUp() {
        parent::setUp();
        $this->verbose = false;
        $this->assumeTestDatabase();
        $this->testUtil->cleanTestData($this->verbose);
        $this->cleanCache();
    }

    public function test_trips_update_and_get_when_no_data() {
        // GIVEN
        $geokretyId = 1234567;
        $tripService = new \Geokrety\Service\TripService($this->mapDirectory, $this->verbose);

        // WHEN
        $tripService->onTripUpdate($geokretyId);
        $trips = $tripService->getTrip($geokretyId);

        // THEN
        $this->assertNotNull($trips);
        $this->assertEmpty($trips);
    }

    public function test_trips_cache() {
        // GIVEN
        $tripService = new \Geokrety\Service\TripService($this->mapDirectory, $this->verbose);
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
        $expectedCount += 2;

        // WHEN
        $trips = $tripService->getTrip($geokretyId);

        // THEN
        $this->assertNotNull($trips);
        $this->assertEquals(count($trips), $expectedCount);

        // WHEN - add trip without re-generating cache
        $this->testUtil->givenRandomTripData($geokretyId, $userIdB, $waypointAB);
        $trips = $tripService->getTrip($geokretyId);

        // THEN - got cache version
        $this->assertNotNull($trips);
        $this->assertEquals(count($trips), $expectedCount);

        // WHEN - evict cache and get again
        $trips = $tripService->evictTripCache($geokretyId);
        ++$expectedCount;
        $trips = $tripService->getTrip($geokretyId);

        // THEN - got fresh version
        $this->assertNotNull($trips);
        $this->assertEquals(count($trips), $expectedCount);
    }
}
