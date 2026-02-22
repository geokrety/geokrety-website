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

            return;
        }

        $love = new GeokretLoveModel();
        $love->geokret = $this->geokret;
        $love->user = $this->current_user;

        try {
            $love->save();
            $success = true;
            $message = _('You have loved this GeoKret! ❤️');
        } catch (\PDOException $e) {
            if ($e->getCode() === '23505') { // unique_violation
                $success = false;
                $message = _('You have already loved this GeoKret.');
            } else {
                throw $e;
            }
        }

        // Reload once (trigger updates loves_count)
        $this->geokret->load(['id = ?', $this->geokret->id]);

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'loves_count' => $this->geokret->loves_count,
        ]);
    }
}
