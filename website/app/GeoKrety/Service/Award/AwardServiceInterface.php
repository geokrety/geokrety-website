<?php

namespace GeoKrety\Service\Award;

use GeoKrety\Model\Move;

interface AwardServiceInterface {
    /**
     * Determine if this move should trigger an award for the user.
     */
    public function shouldAward(Move $move): bool;

    /**
     * Award the user for this move.
     */
    public function awardUser(Move $move): void;
}
