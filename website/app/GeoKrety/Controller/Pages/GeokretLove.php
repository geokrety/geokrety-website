<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\GeokretLove as GeokretLoveModel;
use GeoKrety\Traits\GeokretLoader;

class GeokretLove extends Base {
    use GeokretLoader;

    public function post() {
        $this->checkCsrf();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->geokret->isOwner()) {
            echo json_encode([
                'success' => false,
                'message' => _('You cannot love your own GeoKret.'),
                'loves_count' => $this->geokret->loves_count,
            ]);
            exit;
        }

        $love = new GeokretLoveModel();
        $love->load(['user = ? AND geokret = ?', $this->current_user->id, $this->geokret->id]);

        if (!$love->dry()) {
            // Already loved, return current state
            echo json_encode([
                'success' => false,
                'message' => _('You have already loved this GeoKret.'),
                'loves_count' => $this->geokret->loves_count,
            ]);
            exit;
        }

        $love->geokret = $this->geokret->id;
        $love->user = $this->current_user;
        $love->save();

        // Reload geokret to get the trigger-updated loves_count
        $this->geokret->load(['id = ?', $this->geokret->id]);

        echo json_encode([
            'success' => true,
            'message' => _('You have loved this GeoKret! ❤️'),
            'loves_count' => $this->geokret->loves_count,
        ]);
        exit;
    }
}
