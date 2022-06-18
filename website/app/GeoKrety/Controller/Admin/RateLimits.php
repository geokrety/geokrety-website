<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Service\RateLimit;
use GeoKrety\Service\Smarty;

class RateLimits extends Base {
    public function render_results(\Base $f3) {
        $current = RateLimit::get_rates_limits_usage();
        Smarty::assign('current', $current);
        Smarty::render('admin/pages/api_rate_limit.tpl');
    }
}
