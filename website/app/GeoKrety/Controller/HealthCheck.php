<?php

namespace GeoKrety\Controller;

use GeoKrety\HealthState;
use GeoKrety\Service\Config;

class HealthCheck extends Base {
    protected $state;

    public function __construct() {
        $this->state = new HealthState();
    }

    public function get(\Base $f3) {
        $this->checkWebsiteConfig();
        $this->checkWebsiteDatabase($f3);
        $this->checkDirectoriesPermissions();

        echo $this->state->render();
    }

    private function checkWebsiteConfig() {
        $websiteConfig = Config::instance();
        $validationDetails = null;
        $isConfigOk = $websiteConfig->isValid();
        if (!$isConfigOk) {
            $validationDetails = $websiteConfig->validationDetails();
        }
        $this->state->setDependencyState('config', $isConfigOk ? HealthState::STATE_OK : HealthState::STATE_KO, $validationDetails);
    }

    private function checkWebsiteDatabase(\Base $f3) {
        $isDbOk = $f3->get('DB') !== false;
        $this->state->setDependencyState('db', $isDbOk ? HealthState::STATE_OK : HealthState::STATE_KO);
    }

    private function checkDirectoriesPermissions() {
        $this->state->setDependencyState('assets-dir-perm', is_writable(GK_F3_ASSETS_PUBLIC) ? HealthState::STATE_OK : HealthState::STATE_KO, sprintf('%s must be writable by php', realpath(GK_F3_ASSETS_PUBLIC)));
    }
}
