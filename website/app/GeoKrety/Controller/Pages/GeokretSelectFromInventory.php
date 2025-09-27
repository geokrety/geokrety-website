<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class GeokretSelectFromInventory extends Base {
    public function get($f3) {
        $accept = strtolower((string) ($f3->get('HEADERS.Accept') ?? ''));
        $wants_html = (strpos($accept, 'application/json') !== false) || ($f3->get('GET.format') === 'json');
        if ($wants_html) {
            return $this->get_json($f3);
        }

        // Load owned GeoKrety
        $geokret = new Geokret();
        $filter = ['holder = ? AND parked = ?', $f3->get('SESSION.CURRENT_USER'), null];
        $option = ['order' => 'name ASC'];
        $geokrety = $geokret->find($filter, $option);
        Smarty::assign('geokrety', $geokrety);

        Smarty::render('extends:base_modal.tpl|dialog/geokret_move_select_from_inventory.tpl');
    }

    public function get_json($f3) {
        $geokret = new Geokret();
        $filter = ['holder = ? AND parked = ?', $f3->get('SESSION.CURRENT_USER'), null];
        $option = ['order' => 'name ASC'];
        $rows = $geokret->find($filter, $option);

        // Build a clean array for TomSelect (and any client)
        $out = [];
        foreach ($rows ?: [] as $gk) {
            $out[] = [
                'gkid' => (string) $gk->gkid,
                'name' => $gk->name,
                'tracking_code' => $gk->tracking_code,
                'type' => $gk->type->getTypeId(),
                'collectible' => (bool) $gk->isCollectible(),
                'parked' => (bool) $gk->isParked(),
                'label' => sprintf('%s - %s (%s)', $gk->gkid, $gk->name, $gk->tracking_code),
            ];
        }

        // Always return a JSON array (no numeric object keys)
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_values($out), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
