<?php

namespace GeoKrety;

class HealthState {
    public const HEALTH_STATE_OK = 'ok';
    public const HEALTH_STATE_KO = 'ko';

    public const HEALTH_STATES_VALID = [self::HEALTH_STATE_OK, self::HEALTH_STATE_KO];

    public $state = null;
    public $dependencies = [];

    public function isOk() {
        return $this->state === self::HEALTH_STATE_OK;
    }

    public function refreshState() {
        $this->state = self::HEALTH_STATE_OK;
        foreach ($this->dependencies as $depValue) {
            if ($depValue['state'] != self::HEALTH_STATE_OK) {
                $this->state = self::HEALTH_STATE_KO;

                return;
            }
        }
    }

    public function setDependencyState($dependency, $state, $details = null) {
        $this->checkValidState($state);
        $this->dependencies[$dependency]['state'] = $state;
        if ($this->isOk() && !is_null($details)) {
            $this->dependencies[$dependency]['details'] = $details;
        }
    }

    public function checkValidState($state) {
        if (!in_array($state, self::HEALTH_STATES_VALID)) {
            throw new \Exception("Unhandled state $state");
        }
    }

    public function render() {
        header('Content-Type: application/json; charset=utf-8');
        $this->refreshState();
        if (!$this->isOk()) {
            http_response_code(503);
        }

        return json_encode($this, JSON_UNESCAPED_UNICODE);
    }
}
