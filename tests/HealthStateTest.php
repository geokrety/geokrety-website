<?php

use GeoKrety\HealthState;

class HealthStateTest extends GKTestCase {
    public function test_health_ok() {
        // GIVEN
        $state = new HealthState();

        // WHEN
        $state->refreshState();

        // THEN
        $this->assertNotNull($state);
        $this->assertTrue($state->isOk(), 'Expected health ok');
    }

    public function test_health_unknown() {
        // GIVEN
        // WHEN
        $state = new HealthState();

        // THEN
        $this->assertNotNull($state);
        $this->assertNull($state->state);
    }

    public function test_health_ko() {
        // GIVEN
        $state = new HealthState();
        $state->setDependencyState('depKo', HealthState::HEALTH_STATE_KO, 'just a test, no worry');

        // WHEN
        $state->refreshState();

        // THEN
        $this->assertNotNull($state);
        $this->assertFalse($state->isOk(), 'Expected health ko');
    }
}
