<?php

namespace Geokrety\Service;

const TEMPLATE = 'chunks/geokrety_status.tpl';

/**
 * RuchyValidationService : check Ruchy parameters.
 */
class TrackingCodesValidationService extends AbstractValidationService {
    private $geokretR = null;
    private $errors = array();
    private $geokrety = array();

    public function __construct() {
        $this->geokretR = new \Geokrety\Repository\KonkretRepository(\GKDB::getLink());
    }

    public function getGeokrety() {
        return $this->geokrety;
    }

    public function getErrors() {
        return $this->errors;
    }

    private function buildSmarty() {
        $smarty = new \SmartyBC();
        $smarty->caching = 0;
        $smarty->escape_html = true;
        $smarty->template_dir = './templates/smarty/';
        $smarty->addPluginsDir('./templates/plugins/');
        $smarty->compile_dir = TEMP_DIR_SMARTY_COMPILE;
        $smarty->registerClass('Carbon', '\Carbon\Carbon');

        return $smarty;
    }

    private function checkLength($nr) {
        if (strlen($nr) < 6) {
            array_push($this->errors, sprintf(_('Tracking Code "%s" seems too short. We expect 6 characters here.'), $nr));

            return false;
        }

        return true;
    }

    private function isGKNumber($nr) {
        if (substr($nr, 0, 2) === 'GK') {
            array_push($this->errors, sprintf(_('You seems to have used the GeoKret public identifier "%s". We need the private code (Tracking Code) here. Hint: it doesn\'t starts with \'GK\' 😉'), $nr));

            return false;
        }

        return true;
    }

    private function loockupNr($nr) {
        $geokret = $this->geokretR->getByTrackingCode($nr);
        if (!is_a($geokret, '\Geokrety\Domain\Konkret')) {
            array_push($this->errors, sprintf(_('Sorry, but Tracking Code "%s" was not found in our database.'), $nr));
        } else {
            array_push($this->geokrety, $geokret);
        }
    }

    private function renderGeokret($geokret) {
        $smarty = $this->buildSmarty();
        $smarty->assign('geokret', $geokret);

        $response = array(
            'html' => $smarty->fetch(TEMPLATE),
            'id' => $geokret->id,
            'gkid' => $geokret->getGKId(),
            'nr' => strtoupper($geokret->trackingCode),
            'name' => $geokret->name,
            'description' => $geokret->description,
            'datePublished' => $geokret->datePublished->format('c'),
            'ownerId' => $geokret->ownerId,
            'ownerName' => $geokret->ownerName,
            'holderId' => $geokret->holderId,
            'holderName' => $geokret->holderName,
            'type' => $geokret->type,
            'typeString' => $geokret->typeString,
            'distance' => $geokret->distance,
            'avatarFilename' => $geokret->avatarFilename,
            'avatarCaption' => $geokret->avatarCaption,
            'lastPositionId' => $geokret->lastPositionId,
            'lastLogId' => $geokret->lastLogId,
            'missing' => $geokret->missing,
        );

        return $response;
    }

    public function checkNRs($nrString) {
        $nrArray = explode(',', $nrString);
        $nrArray = array_map('strtoupper', $nrArray);
        $nrArray = array_map('trim', $nrArray);
        $nrArray = array_filter($nrArray);
        $nrArray = array_unique($nrArray);

        if (!sizeof($nrArray)) {
            array_push($this->errors, _('No Tracking Code provided.'));

            return;
        } elseif (!$_SESSION['isLoggedIn'] && sizeof($nrArray) > 1) {
            array_push($this->errors, _('Anonymous users cannot check multiple Tracking Codes at once. Please login first.'));

            return;
        } elseif (sizeof($nrArray) > CHECK_NR_MAX_PROCESSED_ITEMS) {
            array_push($this->errors, sprintf(_('Only %d Tracking Codes may be specified at once, there are %d selected.'), CHECK_NR_MAX_PROCESSED_ITEMS, sizeof($nrArray)));

            return;
        }

        foreach ($nrArray as $nr) {
            if (!$this->isGKNumber($nr)) {
                continue;
            }
            if (!$this->checkLength($nr)) {
                continue;
            }
            $this->loockupNr($nr);
        }
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
