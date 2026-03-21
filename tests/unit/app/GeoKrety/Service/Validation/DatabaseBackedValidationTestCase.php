<?php

namespace unit\app\GeoKrety\Service\Validation;

use GeoKrety\GeokretyType;
use GeoKrety\Model\User;
use Mockery;

abstract class DatabaseBackedValidationTestCase extends Mockery\Adapter\Phpunit\MockeryTestCase {
    private static int $nextId = 900000;

    protected function setUp(): void {
        parent::setUp();

        $this->db()->exec('BEGIN');
        \Base::instance()->set('SESSION.CURRENT_USER', null);
        \Base::instance()->set('CURRENT_USER', null);
    }

    protected function tearDown(): void {
        \Base::instance()->set('SESSION.CURRENT_USER', null);
        \Base::instance()->set('CURRENT_USER', null);
        $this->db()->exec('ROLLBACK');

        parent::tearDown();
    }

    protected function db() {
        return \Base::instance()->get('DB');
    }

    protected function insertUser(
        string $username,
        ?string $email = null,
        int $accountValid = User::USER_ACCOUNT_ACTIVATED,
    ): int {
        $id = $this->nextId();

        if ($email === null) {
            $this->db()->exec(
                'INSERT INTO gk_users (id, username, registration_ip, preferred_language, account_valid) VALUES (?, ?, ?, ?, ?)',
                [$id, $username, '127.0.0.1', 'en', $accountValid]
            );
        } else {
            $this->db()->exec(
                'INSERT INTO gk_users (id, username, registration_ip, preferred_language, account_valid, _email) VALUES (?, ?, ?, ?, ?, ?)',
                [$id, $username, '127.0.0.1', 'en', $accountValid, $email]
            );
        }

        return $id;
    }

    protected function insertGeokret(
        string $trackingCode,
        string $name = 'Test GeoKret',
        int $type = GeokretyType::GEOKRETY_TYPE_TRADITIONAL,
        ?int $ownerId = null,
        ?int $holderId = null,
        ?string $mission = 'Mission text',
    ): int {
        $id = $this->nextId();

        $this->db()->exec(
            'INSERT INTO gk_geokrety (id, gkid, tracking_code, name, mission, owner, holder, type, created_on_datetime, born_on_datetime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $id,
                $id,
                $trackingCode,
                $name,
                $mission,
                $ownerId,
                $holderId,
                $type,
                '2024-01-02 03:04:05+00',
                '2024-01-03 04:05:06+00',
            ]
        );

        return $id;
    }

    protected function insertWaypointGc(
        string $waypoint,
        float $lat = 52.22968,
        float $lon = 21.01223,
        string $country = 'PL',
    ): int {
        $id = $this->nextId();

        $this->db()->exec(
            'INSERT INTO gk_waypoints_gc (id, waypoint, lat, lon, country) VALUES (?, ?, ?, ?, ?)',
            [$id, strtoupper($waypoint), $lat, $lon, strtoupper($country)]
        );

        return $id;
    }

    protected function insertWaypointOc(
        string $waypoint,
        float $lat = 50.0755,
        float $lon = 14.4378,
        string $country = 'CZ',
    ): int {
        $id = $this->nextId();

        $this->db()->exec(
            'INSERT INTO gk_waypoints_oc (id, waypoint, lat, lon, country) VALUES (?, ?, ?, ?, ?)',
            [$id, strtoupper($waypoint), $lat, $lon, strtoupper($country)]
        );

        return $id;
    }

    private function nextId(): int {
        return self::$nextId++;
    }
}
