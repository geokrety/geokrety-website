<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Service\RateLimit;
use GeoKrety\Service\Smarty;

class RateLimitsReset extends Base {
    public function get(\Base $f3) {
        Smarty::assign('name', $f3->get('PARAMS.name'));
        Smarty::assign('key', $f3->get('PARAMS.key'));
        Smarty::render('extends:base_modal.tpl|dialog/admin_dialog_rate_limit_reset.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf(function ($error) use ($f3) {
            \Flash::instance()->addMessage($error, 'danger');
            $f3->reroute('@admin_api_rate_limits');
        });

        $name = $f3->get('PARAMS.name');
        $key = $f3->get('PARAMS.key');
        RateLimit::reset($name, $key);
        \Flash::instance()->addMessage(_('Rate limit has been reset'), 'success');
        \Sugar\Event::instance()->emit('rate-limit.reset', ['name' => $name, 'key' => $key]);
        $f3->reroute('@admin_api_rate_limits');
    }
}
