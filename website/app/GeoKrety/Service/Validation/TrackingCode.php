<?php

namespace GeoKrety\Service\Validation;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;

class TrackingCode {
    private $errors = array();
    private $geokrety = array();

    public function getGeokrety() {
        return $this->geokrety;
    }

    public function getErrors() {
        return $this->errors;
    }

    private function checkLength($trackingCode) {
        if (strlen($trackingCode) < GK_SITE_TRACKING_CODE_LENGTH) {
            array_push($this->errors, sprintf(_('Tracking Code "%s" seems too short. We expect %d characters here.'), $trackingCode, GK_SITE_TRACKING_CODE_LENGTH));

            return false;
        }

        return true;
    }

    private function isGKNumber($trackingCode) {
        if (substr($trackingCode, 0, 2) === 'GK') {
            array_push($this->errors, sprintf(_('You seems to have used the GeoKret public identifier "%s". We need the private code (Tracking Code) here. Hint: it doesn\'t starts with \'GK\' 😉'), $trackingCode));

            return false;
        }

        return true;
    }

    protected function checkCharacters($trackingCode) {
        if (!preg_match('/[^A-Za-z0-9]+/', $trackingCode)) {
            return true;
        }
        $this->errors[] = sprintf(_('Tracking Code "%s" contains invalid characters.'), $trackingCode);

        return false;
    }

    private function lookupTrackingCode($trackingCode) {
        $geokret = new Geokret();
        $geokret->load(array('tracking_code = ?', $trackingCode));

        if ($geokret->dry()) {
            array_push($this->errors, sprintf(_('Sorry, but Tracking Code "%s" was not found in our database.'), $trackingCode));

            return;
        }
        array_push($this->geokrety, $geokret);
    }

    private function renderGeokret($geokret) {
        Smarty::assign('geokret', $geokret);
        $response = array(
            'html' => Smarty::fetch('chunks/geokrety_status.tpl'),
            'id' => $geokret->gkid(),
            'gkid' => $geokret->gkid,
            'nr' => $geokret->tracking_code,
            'name' => $geokret->name,
            'mission' => $geokret->mission,
            'createdOnDatetime' => $geokret->created_on_datetime->format('c'),
            'ownerId' => $geokret->owner->id,
            'ownerName' => is_null($geokret->owner) ? null : $geokret->owner->username,
            'holderId' => is_null($geokret->holder) ? null : $geokret->holder->id,
            'holderName' => is_null($geokret->holder) ? null : $geokret->holder->username,
            'type' => $geokret->type->getTypeId(),
            'typeString' => $geokret->type->getTypeString(),
            'distance' => $geokret->distance,
            // 'avatarFilename' => $geokret->avatarFilename,
            // 'avatarCaption' => $geokret->avatarCaption,
            'lastPositionId' => is_null($geokret->last_position) ? null : $geokret->last_position->id,
            'lastLogId' => is_null($geokret->last_log) ? null : $geokret->last_log->id,
            'missing' => $geokret->missing,
        );

        return $response;
    }

    public function validate($trackingCodeString) {
        $f3 = \Base::instance();

        $trackingCodeArray = explode(',', $trackingCodeString);
        $trackingCodeArray = array_map('strtoupper', $trackingCodeArray);
        $trackingCodeArray = array_map('trim', $trackingCodeArray);
        $trackingCodeArray = array_filter($trackingCodeArray);
        $trackingCodeArray = array_unique($trackingCodeArray);

        $trackingCodeCount = sizeof($trackingCodeArray);
        if (!$trackingCodeCount) {
            array_push($this->errors, _('No Tracking Code provided.'));

            return false;
        } elseif (!$f3->get('SESSION.CURRENT_USER') && $trackingCodeCount > 1) {
            array_push($this->errors, _('Anonymous users cannot check multiple Tracking Codes at once. Please login first.'));

            return false;
        } elseif ($trackingCodeCount > GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS) {
            array_push($this->errors, sprintf(_('Only %d Tracking Codes may be specified at once, there are %d selected.'), GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS, $trackingCodeCount));

            return false;
        }

        foreach ($trackingCodeArray as $trackingCode) {
            if (!$this->checkCharacters($trackingCode)) {
                continue;
            }
            if (!$this->isGKNumber($trackingCode)) {
                continue;
            }
            if (!$this->checkLength($trackingCode)) {
                continue;
            }
            $this->lookupTrackingCode($trackingCode);
        }

        return true;
    }

    public function render() {
        header('Content-Type: application/json; charset=utf-8');
        if (sizeof($this->errors)) {
            http_response_code(400);

            return json_encode($this->errors, JSON_UNESCAPED_UNICODE);
        }
        $response = array();
        foreach ($this->geokrety as $geokret) {
            array_push($response, $this->renderGeokret($geokret));
        }

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
