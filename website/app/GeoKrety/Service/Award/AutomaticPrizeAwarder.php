<?php

namespace GeoKrety\Service\Award;

use GeoKrety\Model\Move;

class AutomaticPrizeAwarder {
    /**
     * Array of award service classes to process.
     */
    private array $awardServices = [
        EasterEggAwardService::class,
        // Future award services can be added here
    ];

    /**
     * Process all automatic awards for a given move.
     */
    public static function processMove(Move $move): void {
        $awarder = new self();
        $awarder->handleMove($move);
    }

    /**
     * Handle move processing by iterating through all award services.
     */
    private function handleMove(Move $move): void {
        // Only process moves with an author (logged-in users)
        if ($move->author === null) {
            return;
        }

        foreach ($this->awardServices as $serviceClass) {
            try {
                $service = new $serviceClass();

                if ($service instanceof AwardServiceInterface) {
                    if ($service->shouldAward($move)) {
                        $service->awardUser($move);
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't break move processing
                // Awards are nice-to-have, not critical functionality
                error_log("Award service error for {$serviceClass}: ".$e->getMessage());
            }
        }
    }
}
