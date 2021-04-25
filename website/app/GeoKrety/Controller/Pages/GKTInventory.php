<?php

namespace GeoKrety\Controller;

class GKTInventory extends BaseGKT {
    /**
     * @var mixed
     */
    private $current_user_id;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->geokrety = [
            'loggedin' => $this->isLoggedIn(),
            'list' => [],
        ];
    }

    public function isLoggedIn(): bool {
        $sql = 'SELECT data FROM sessions WHERE on_behalf = ?';
        $result = $this->f3->get('DB')->exec($sql, [$this->f3->get('COOKIE.gkt_on_behalf')]);
        if (sizeof($result) === 0) {
            return false;
        }
        session_decode($result[0]['data']);

        return $this->current_user_id = $_SESSION['CURRENT_USER'];
    }

    public function get(\Base $f3) {
        if ($this->isLoggedIn()) {
            $this->setFilter(
                'holder = ?',
                $this->current_user_id,
            );
            $this->loadGeokrety();
        }

        $this->render();
    }

    protected function processAddGeokret(&$geokret) {
        $this->geokrety['list'][] = [
            'tc' => $geokret->tracking_code,
            'n' => $geokret->name,
        ];
    }
}
