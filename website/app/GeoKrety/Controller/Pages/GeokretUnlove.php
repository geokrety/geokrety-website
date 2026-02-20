<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\GeokretLove as GeokretLoveModel;
use GeoKrety\Traits\GeokretLoader;

class GeokretUnlove extends Base {
    use GeokretLoader;

    public function post() {
        $this->checkCsrf();
        header('Content-Type: application/json; charset=utf-8');

        $love = new GeokretLoveModel();
        $love->load(['user = ? AND geokret = ?', $this->current_user->id, $this->geokret->id]);

        if ($love->dry()) {
            // Not loved, return current state
            echo json_encode([
                'success' => false,
                'message' => _('You have not loved this GeoKret.'),
                'loves_count' => $this->geokret->loves_count,
            ]);
            exit;
        }

        $love->erase();

        // Reload geokret to get the trigger-updated loves_count
        $this->geokret->load(['id = ?', $this->geokret->id]);

        echo json_encode([
            'success' => true,
            'message' => _('You have removed your love for this GeoKret.'),
            'loves_count' => $this->geokret->loves_count,
        ]);
        exit;
    }
}
