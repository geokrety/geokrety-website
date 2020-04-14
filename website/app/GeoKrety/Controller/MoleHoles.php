<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\WaypointOC;
use GeoKrety\Service\Smarty;

class MoleHoles extends Base {
    public function get() {
        $moleholesInput = [
            'OP1891', 'OC7DCB', 'OP3373', 'OCBEA7',
            'OP3B15', 'OCCB8A', 'OCC3EC', 'OCC8B7',
        ];
        $moleholes = [];
        $wpt = new WaypointOC();
        foreach ($moleholesInput as $waypoint) {
            $wpt->reset();
            $wpt->load(['waypoint = ?', $waypoint]);
            if ($wpt->valid()) {
                $moleholes[$waypoint] = [
                    $wpt->name, $wpt->type, $wpt->country_name,
                    $wpt->link, $wpt->country, $wpt->status,
                ];
            }
        }
        Smarty::assign('moleholes', $moleholes);

        $molehotelsInput = [
            'OP0F87', 'OP2262', 'OP16B8', 'OC85FC', 'OK0085', 'OP1C23',
            'OP115F', 'OP0F87', 'OP183D', 'OP187D', 'OP1990', 'OP0982',
            'OP10F0', 'OC5895', 'OZ0140', 'OP1EAC', 'OP20A0', 'OP1D0E',
            'OP229C', 'OP1E84', 'OP18F2', 'OP1B2C', 'OP230E', 'OP2017',
            'OP239D', 'OP1B73', 'OP2387', 'OP183D', 'OP1328', 'OP1891',
            'OP16B8', 'OP115F', 'OP2221', 'OP1217', 'OP2406', 'OP2423',
            'OP2061', 'OP1FD6', 'OP2461', 'OP22E6', 'OP23CA', 'OP19E0',
            'GR0433', 'OCBD6B', 'OCBC73', 'OC921F', 'OCA814', 'OU035B',
            'OU036C', 'OU02CC', 'OU02D5', 'OU02DD', 'OU0349', 'OU02C3',
            'OU01BD', 'OU035B', 'OU024C', 'OU0318', 'OB1466', 'TR17200',
            'TR22853', 'TR22854', 'TR17299', 'TR17436', 'TR17479', 'TR18111',
            'TR18165',
        ];

        $gkhotels = [];
        foreach ($molehotelsInput as $waypoint) {
            $wpt->reset();
            $wpt->load(['waypoint = ?', $waypoint]);
            if ($wpt->valid()) {
                $gkhotels[$waypoint] = [
                    $wpt->name, $wpt->type, $wpt->country_name,
                    $wpt->link, $wpt->country, $wpt->status,
                ];
            }
        }
        Smarty::assign('gkhotels', $gkhotels);

        Smarty::render('pages/mole_hole.tpl');
    }
}
