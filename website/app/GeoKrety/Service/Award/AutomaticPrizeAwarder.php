<?php

namespace GeoKrety\Service\Award;

use GeoKrety\Model\Awards;
use GeoKrety\Model\AwardsWon;
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
                        $this->awardUser($move, $service);
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't break move processing
                // Awards are nice-to-have, not critical functionality
                error_log("Award service error for {$serviceClass}: ".$e->getMessage());
            }
        }
    }

    /**
     * Award the user using the provided service.
     */
    private function awardUser(Move $move, AwardServiceInterface $service): void {
        // Load award with cache (1 hour TTL)
        $award = new Awards();
        $award->load(['name = ?', $service->getAwardName()], ttl: 3600);

        if ($award->dry()) {
            // Award doesn't exist - log and return
            error_log(sprintf("Award '%s' not found in database", $service->getAwardName()));

            return;
        }

        // Check if already awarded (with cache)
        $awardWon = new AwardsWon();
        $awardWon->load(['holder = ? and award = ?', $move->author->id, $award->id], ttl: 3600);

        if (!$awardWon->dry()) {
            return; // Already awarded
        }

        // Create and save award
        $awardWon = new AwardsWon();
        $awardWon->holder = $move->author->id;
        $awardWon->award = $award->id;
        $awardWon->description = $service->getAwardDescription($move);
        $awardWon->save();

        // Invalidate cache immediately
        $invalidateCheck = new AwardsWon();
        $invalidateCheck->load(['holder = ? and award = ?', $move->author->id, $award->id], ttl: -1);

        // Fire event for audit trail
        $events = \Sugar\Event::instance();
        $events->emit('award.given', [
            'award_id' => $award->id,
            'award_name' => $service->getAwardName(),
            'user_id' => $move->author->id,
            'move_id' => $move->id,
            'geokret_id' => $move->geokret->id,
            'automatic' => true,
        ]);
    }
}
