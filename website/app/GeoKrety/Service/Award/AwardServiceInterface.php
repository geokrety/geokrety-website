<?php

namespace GeoKrety\Service\Award;

use GeoKrety\Model\Move;

interface AwardServiceInterface {
    /**
     * Determine if this move should trigger an award for the user.
     */
    public function shouldAward(Move $move): bool;

    /**
     * Get the award name for this service.
     */
    public function getAwardName(): string;

    /**
     * Get the award description for this move.
     */
    public function getAwardDescription(Move $move): string;
}
