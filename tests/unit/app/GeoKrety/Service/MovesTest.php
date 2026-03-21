<?php

namespace unit\app\GeoKrety\Service;

require_once __DIR__.'/Validation/DatabaseBackedValidationTestCase.php';

use GeoKrety\LogType;
use GeoKrety\Model\Move;
use GeoKrety\Service\Moves;
use unit\app\GeoKrety\Service\Validation\DatabaseBackedValidationTestCase;

class MovesTest extends DatabaseBackedValidationTestCase {
    private int $userId;
    private string $trackingCode;

    protected function setUp(): void {
        parent::setUp();

        $this->userId = $this->insertUser('moves-service-user');
        $this->trackingCode = 'ABCD12';
        $this->insertGeokret($this->trackingCode, ownerId: $this->userId, holderId: $this->userId);
        \Base::instance()->set('SESSION.CURRENT_USER', $this->userId);
    }

    public function testSeenWithCoordinatesAndNoWaypointKeepsCoordinates(): void {
        [$moves, $errors] = $this->toMoves([
            'coordinates' => '57.462633 22.849983',
            'logtype' => LogType::LOG_TYPE_SEEN,
            'waypoint' => '',
        ]);

        $this->assertSame([], $errors);
        $this->assertCount(1, $moves);
        $this->assertNull($moves[0]->waypoint);
        $this->assertSame('57.46263', $moves[0]->lat);
        $this->assertSame('22.84998', $moves[0]->lon);
    }

    public function testSeenWithInvalidCoordinatesAndNoWaypointFailsValidation(): void {
        [$moves, $errors] = $this->toMoves([
            'coordinates' => 'malformed-coordinates',
            'logtype' => LogType::LOG_TYPE_SEEN,
            'waypoint' => '',
        ]);

        $this->assertCount(1, $moves);
        $this->assertContains('Bad coordinates or unknown format.', $errors);
        $this->assertNull($moves[0]->waypoint);
        $this->assertNull($moves[0]->lat);
        $this->assertNull($moves[0]->lon);
    }

    public function testSeenWithWaypointAndCoordinateOverrideKeepsBoth(): void {
        $this->insertWaypointGc('GCUNIT1', 12.34, 56.78, 'FR');

        [$moves, $errors] = $this->toMoves([
            'coordinates' => '57.462633 22.849983',
            'logtype' => LogType::LOG_TYPE_SEEN,
            'waypoint' => 'gcunit1',
        ]);

        $this->assertSame([], $errors);
        $this->assertCount(1, $moves);
        $this->assertSame('GCUNIT1', $moves[0]->waypoint);
        $this->assertSame('57.46263', $moves[0]->lat);
        $this->assertSame('22.84998', $moves[0]->lon);
    }

    public function testSeenWithoutWaypointOrCoordinatesRemainsLocationless(): void {
        [$moves, $errors] = $this->toMoves([
            'coordinates' => '',
            'logtype' => LogType::LOG_TYPE_SEEN,
            'waypoint' => '',
        ]);

        $this->assertSame([], $errors);
        $this->assertCount(1, $moves);
        $this->assertNull($moves[0]->waypoint);
        $this->assertNull($moves[0]->lat);
        $this->assertNull($moves[0]->lon);
    }

    public function testCommentWithCoordinatesStillClearsLocation(): void {
        [$moves, $errors] = $this->toMoves([
            'coordinates' => '57.462633 22.849983',
            'logtype' => LogType::LOG_TYPE_COMMENT,
            'waypoint' => '',
        ]);

        $this->assertSame([], $errors);
        $this->assertCount(1, $moves);
        $this->assertNull($moves[0]->waypoint);
        $this->assertNull($moves[0]->lat);
        $this->assertNull($moves[0]->lon);
    }

    /**
     * @return array{0: array<int, Move>, 1: array<int, string>}
     */
    private function toMoves(array $override): array {
        \Base::instance()->clear('validation.error');

        $moveData = array_replace($this->baseMoveData(), $override);

        return (new Moves())->toMoves($moveData, new Move());
    }

    private function baseMoveData(): array {
        return [
            'app' => 'phpunit',
            'app_ver' => '1.0.0',
            'comment' => 'Test move comment',
            'comment_hidden' => false,
            'coordinates' => '',
            'date' => '2024-02-04',
            'hour' => '12',
            'logtype' => LogType::LOG_TYPE_SEEN,
            'minute' => '00',
            'second' => '00',
            'tracking_code' => $this->trackingCode,
            'tz' => 'UTC',
            'username' => null,
            'waypoint' => '',
        ];
    }
}
