<?php

use GeoKrety\HealthState;

class HealthStateTest extends GKTestCase {
    public function testHealthOk() {
        // GIVEN
        $state = new HealthState();

        // WHEN
        $state->refreshState();

        // THEN
        $this->assertNotNull($state);
        $this->assertTrue($state->isOk(), 'Expected health ok');
    }

    public function testHealthUnknown() {
        // GIVEN
        // WHEN
        $state = new HealthState();

        // THEN
        $this->assertNotNull($state);
        $this->assertNull($state->state);
    }

    public function testHealthKo() {
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
