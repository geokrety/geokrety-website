<?php

namespace GeoKrety\Service\Award;

use GeoKrety\GeokretyType;
use GeoKrety\LogType;
use GeoKrety\Model\Move;

class EasterEggAwardService implements AwardServiceInterface {
    private const AWARD_NAME = 'Hidden GeoKrety Finder';

    /**
     * Check if this move should trigger the Easter Egg award.
     */
    public function shouldAward(Move $move): bool {
        return $move->author !== null
            && $move->geokret !== null
            && $move->geokret->type->getTypeId() === GeokretyType::GEOKRETY_TYPE_EASTER_EGG
            && $move->move_type->getLogTypeId() === LogType::LOG_TYPE_SEEN;
    }

    /**
     * Get the award name for this service.
     */
    public function getAwardName(): string {
        return self::AWARD_NAME;
    }

    /**
     * Get the award description for this move.
     */
    public function getAwardDescription(Move $move): string {
        return 'Automatically awarded for discovering a Hidden GeoKrety';
    }
}
