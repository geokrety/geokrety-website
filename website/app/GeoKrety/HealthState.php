<?php

namespace GeoKrety;

class HealthState {
    const STATE_OK = 'ok';
    const STATE_KO = 'ko';
    const VALID_STATES = [self::STATE_OK, self::STATE_KO];

    public $state = null;
    public $dependencies = [];

    public function isOk() {
        return $this->state === self::STATE_OK;
    }

    public function refreshState() {
        $this->state = self::STATE_OK;
        foreach ($this->dependencies as $depValue) {
            if ($depValue['state'] != self::STATE_OK) {
                $this->state = self::STATE_KO;

                return;
            }
        }
    }

    public function setDependencyState($dependency, $state, $details = null) {
        $this->checkValidState($state);
        $this->dependencies[$dependency]['state'] = $state;
        if (!is_null($details)) {
            $this->dependencies[$dependency]['details'] = $details;
        }
    }

    public function checkValidState($state) {
        if (!in_array($state, self::VALID_STATES)) {
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
