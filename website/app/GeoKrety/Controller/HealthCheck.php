<?php

namespace GeoKrety\Controller;

use GeoKrety\HealthState;

class HealthCheck extends Base {
    public function get($f3) {
        $state = new HealthState();
        $websiteConfig = new \GeoKrety\Service\Config();

        //~ dependencies checks
        $configValidationDetails = null;
        $isConfigOk = $websiteConfig->isValid();
        if (!$isConfigOk) {
            $configValidationDetails = $websiteConfig->validationDetails();
        }

        $isDbOk = $f3->get('DB') !== false;

        //~ update state
        $state->setDependencyState('config', $isConfigOk ? HealthState::STATE_OK : HealthState::STATE_KO, $configValidationDetails);
        $state->setDependencyState('db', $isDbOk ? HealthState::STATE_OK : HealthState::STATE_KO);

        //~ produce response
        if (!$state->isOk()) {
            http_response_code(503);
        }
        echo json_encode($state);
    }
}
