<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class SearchAdvanced extends Base {
    public function get($f3) {
        Smarty::render('pages/advanced_search.tpl');
    }

    public function post($f3) {
        $search = strtoupper($f3->get('POST.inputSearch'));
        Smarty::assign('search', $search);

        $this->get($f3);
    }
}
