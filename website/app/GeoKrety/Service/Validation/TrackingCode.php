<?php

namespace GeoKrety\Service\Validation;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class TrackingCode {
    private array $errors = [];
    private array $geokrety = [];

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
        if (strlen($trackingCode) > GK_SITE_TRACKING_CODE_LENGTH) {
            array_push($this->errors, sprintf(_('Tracking Code "%s" seems too long. We expect %d characters here.'), $trackingCode, GK_SITE_TRACKING_CODE_LENGTH));

            return false;
        }

        return true;
    }

    private function isGKNumber($trackingCode) {
        if (substr($trackingCode, 0, 2) === 'GK') {
            if (strlen($trackingCode) >= GK_SITE_TRACKING_CODE_LENGTH) {
                $geokret = new Geokret();
                $geokret->load(['tracking_code = ?', $trackingCode]);

                if (!$geokret->dry()) {
                    return true;
                }
            }
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
        $geokret->load(['tracking_code = ?', $trackingCode]);

        if ($geokret->dry()) {
            array_push($this->errors, sprintf(_('Sorry, but Tracking Code "%s" was not found in our database.'), $trackingCode));

            return false;
        }
        array_push($this->geokrety, $geokret);

        return true;
    }

    private function renderGeokret(Geokret $geokret) {
        Smarty::assign('geokret', $geokret);
        $response = [
            'html' => Smarty::fetch('chunks/geokrety_status.tpl'),
            'id' => $geokret->gkid(),
            'gkid' => $geokret->gkid,
            'nr' => $geokret->tracking_code,
            'name' => $geokret->name,
            'mission' => $geokret->mission,
            'createdOnDatetime' => $geokret->created_on_datetime->format('c'),
            'ownerId' => $geokret->owner->id ?? null,
            'ownerName' => $geokret->owner ? $geokret->owner->username : null,
            'holderId' => $geokret->holder ? $geokret->holder->id : null,
            'holderName' => $geokret->holder ? $geokret->holder->username : null,
            'type' => $geokret->type->getTypeId(),
            'typeString' => $geokret->type->getTypeString(),
            'distance' => $geokret->distance,
            // 'avatarFilename' => $geokret->avatarFilename,
            // 'avatarCaption' => $geokret->avatarCaption,
            'lastPositionId' => $geokret->last_position ? $geokret->last_position->id : null,
            'lastLogId' => $geokret->last_log ? $geokret->last_log->id : null,
            'missing' => $geokret->missing,
        ];

        return $response;
    }

    public static function split_tracking_codes(?string $trackingCodeString): array {
        $trackingCodeArray = explode(',', $trackingCodeString);
        $trackingCodeArray = array_map('strtoupper', $trackingCodeArray);
        $trackingCodeArray = array_map('trim', $trackingCodeArray);
        $trackingCodeArray = array_filter($trackingCodeArray);

        return array_unique($trackingCodeArray);
    }

    public function validate($trackingCodeString) {
        $f3 = \Base::instance();

        $trackingCodeArray = self::split_tracking_codes($trackingCodeString);

        $trackingCodeCount = sizeof($trackingCodeArray);
        if ($trackingCodeCount === 0) {
            array_push($this->errors, _('No Tracking Code provided.'));

            return false;
        } elseif (!$f3->get('SESSION.CURRENT_USER') && $trackingCodeCount > 1) {
            array_push($this->errors, _('Anonymous users cannot check multiple Tracking Codes at once. Please login first.'));

            return false;
        } elseif ($trackingCodeCount > GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS) {
            array_push($this->errors, sprintf(_('Only %d Tracking Codes may be specified at once, there are %d selected.'), GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS, $trackingCodeCount));

            return false;
        }

        $return_status = true;
        foreach ($trackingCodeArray as $trackingCode) {
            if (!$this->checkCharacters($trackingCode)) {
                $return_status = false;
                continue;
            }
            if (!$this->isGKNumber($trackingCode)) {
                $return_status = false;
                continue;
            }
            if (!$this->checkLength($trackingCode)) {
                $return_status = false;
                continue;
            }
            if (!$this->lookupTrackingCode($trackingCode)) {
                $return_status = false;
                continue;
            }
        }

        return $return_status;
    }

    public function render() {
        header('Content-Type: application/json; charset=utf-8');
        if (sizeof($this->errors)) {
            http_response_code(400);

            return json_encode($this->errors, JSON_UNESCAPED_UNICODE);
        }
        $response = [];
        foreach ($this->geokrety as $geokret) {
            array_push($response, $this->renderGeokret($geokret));
        }

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
