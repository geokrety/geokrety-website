<?php

namespace GeoKrety\Controller;

use GeoKrety\HealthState;

class HealthCheck extends Base {
    protected $state;

    public function get($f3) {
        $this->state = new HealthState();

        $this->checkWebsiteConfig();
        $this->checkWebsiteDatabase($f3);

        echo $this->state->render();
    }

    private function checkWebsiteConfig() {
        $websiteConfig = new \GeoKrety\Service\Config();
        $validationDetails = null;
        $isConfigOk = $websiteConfig->isValid();
        if (!$isConfigOk) {
            $validationDetails = $websiteConfig->validationDetails();
        }
        $this->state->setDependencyState('config', $isConfigOk ? HealthState::STATE_OK : HealthState::STATE_KO, $validationDetails);
    }

    private function checkWebsiteDatabase($f3) {
        $isDbOk = $f3->get('DB') !== false;
        $this->state->setDependencyState('db', $isDbOk ? HealthState::STATE_OK : HealthState::STATE_KO);
    }
}
