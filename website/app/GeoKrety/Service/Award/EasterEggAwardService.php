<?php

namespace GeoKrety\Service\Award;

use GeoKrety\GeokretyType;
use GeoKrety\LogType;
use GeoKrety\Model\Awards;
use GeoKrety\Model\AwardsWon;
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
     * Award the Hidden GeoKrety Finder award to the user.
     */
    public function awardUser(Move $move): void {
        // Load award with cache (1 hour TTL)
        $award = new Awards();
        $award->load(['name = ?', self::AWARD_NAME], ttl: 3600);

        if ($award->dry()) {
            // Award doesn't exist - log and return
            error_log("Award '{self::AWARD_NAME}' not found in database");

            return;
        }

        // Create award assignment
        $awardWon = new AwardsWon();
        $awardWon->holder = $move->author->id;
        $awardWon->award = $award->id;
        $awardWon->description = 'Automatically awarded for discovering a Hidden GeoKrety';

        try {
            $awardWon->save();

            // Fire event for audit trail
            $events = \Sugar\Event::instance();
            $events->emit('award.given', [
                'award_id' => $award->id,
                'award_name' => self::AWARD_NAME,
                'user_id' => $move->author->id,
                'move_id' => $move->id,
                'geokret_id' => $move->geokret->id,
                'automatic' => true,
            ]);
        } catch (\Exception $e) {
            // Database constraint violation (duplicate award) - silently ignore
            // Other errors are also ignored to not break move processing
            // This is logged in AutomaticPrizeAwarder
        }
    }
}
