<?php

namespace Geokrety\Domain;

use Geokrety\Repository\TripRepository;
use \Geokrety\Domain\Konkret;
use \Geokrety\Domain\LogType;

include_once 'aktualizuj.php';
include_once 'konkret-mapka.php';

class TripStep extends AbstractObject {
    public $lat; // latitude
    public $lon; // longitude
    public $alt = -32768; // altitude

    //~ ruchy centric
    public $ruchId; // ruchy entry id
    public $ruchData; // ruchy user provided date
    public $ruchDataDodania; // ruchy database added date

    public $userId; // ruchy author user id
    public $username; // ruchy author username
    public $comment; // ruchy username comment
    public $logType; // 0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip
    public $country; // country code
    public $distance = 0; // road traveled in km

    //~ geokret centric
    public $geokretId; // geokret id
    public $geokretNr; // geokret tracking code
    public $geokret; // Konkret object

    public $app; // application name
    public $appVer; // application version
    public $picturesCount = 0; // number of pictures
    public $commentsCount = 0; // number of comments

    //~ waypoint centric
    public $waypoint; // waypoint code (optional)
    public $waypointName;
    public $waypointType; // ...
    public $waypointOwner;
    public $waypointStatus;
    public $waypointLink;

    //~ calculated attributes
    public $logTypeString;
    public $htmlContent;

    public function __construct($waypoint = null) {
        $this->waypoint = $waypoint;
    }

    public function getUrl($anchor = null) {
        $anchor_ = '';
        if (is_null($anchor)) {
            $anchor_ = '#log'.$this->ruchId;
        } else {
            $anchor_ = '#'.$anchor;
        }

        $geokretUrl = $this->geokret->geturl();

        $tripR = new TripRepository(\GKDB::getLink());
        $page = '&page='.$tripR->getLogOnPageNumber($this->ruchId);

        return $geokretUrl.$page.$anchor_;
    }

    public function editUrl() {
        return sprintf('ruchy.php?edit=1&ruchid=%d', $this->ruchId);
    }

    public function getWaypoint() {
        return trim(strtoupper($this->waypoint));
    }

    public function getLat() {
        return $this->lat ? number_format($this->lat, 5, '.', '') : null;
    }

    public function getLon() {
        return $this->lon ? number_format($this->lon, 5, '.', '') : null;
    }

    public function setComment(?string $comment) {
        // Workaround historical database modifications
        $txt = str_replace('<br />', '  ', $comment);
        $txt = str_replace('[<a href=\'', '', $txt);
        $txt = str_replace('\' rel=nofollow>Link</a>]', '', $txt);
        $this->comment = $txt;
    }

    public function getCoordinates() {
        if (!$this->lat || !$this->lon) {
            return array();
        }

        return array($this->getLat(), $this->getLon());
    }

    public function getDate($format = 'Y-m-d H:i:s') {
        if (is_a($this->ruchData, '\Datetime')) {
            return $this->ruchData->format($format);
        }

        return $this->ruchData;
    }

    public function getDateLogged() {
        if (is_a($this->ruchDataDodania, '\Datetime')) {
            return $this->ruchDataDodania->format('Y-m-d H:i:s');
        }

        return $this->ruchDataDodania;
    }

    public function setDate($date, $format = 'Y-m-d H:i:s') {
        if (is_a($date, '\Datetime')) {
            $this->ruchData = $date;
            return;
        }
        $this->ruchData = \DateTime::createFromFormat($format, $date, new \DateTimeZone('UTC'));
    }

    public function setDateLogged($date, $format = 'Y-m-d H:i:s') {
        if (is_a($date, '\Datetime')) {
            $this->ruchDataDodania = $date;
            return;
        }
        $this->ruchDataDodania = \DateTime::createFromFormat($format, $date, new \DateTimeZone('UTC'));
    }

    public function setLogtype($logtype) {
        $this->logType = new \Geokrety\Domain\LogType($logtype);
        $this->resetFields();
    }

    public function isType($logtype) {
        if (is_null($this->logType)) {
            return false;
        }

        return $this->logType->isType($logtype);
    }

    protected function resetFields() {
        if ($this->logType->isCoordinatesRequired()) {
            return;
        }
        $this->lat = null;
        $this->lon = null;
        $this->alt = null;
        $this->waypoint = null;
        $this->country = null;
        $this->distance = 0;
    }

    public function save() {
        $dbIsSuccess = false;
        if (is_null($this->ruchId)) {
            $dbIsSuccess = $this->insert();
        } else {
            $tripR = new \Geokrety\Repository\TripRepository(\GKDB::getLink());
            $tripStep = $tripR->getByTripId($this->ruchId);
            if (is_null($tripStep)) {
                $_SESSION['alert_msgs'][] = array(
                    'level' => 'danger',
                    'message' => _('No such move id.'),
                );

                return false;
            }
            if (!$tripStep->isAuthor()) {
                $_SESSION['alert_msgs'][] = array(
                    'level' => 'danger',
                    'message' => _('You cannot edit a move which you are not the owner.'),
                );

                return false;
            }

            $dbIsSuccess = $this->update();
        }

        if ($dbIsSuccess) {
            $_SESSION['alert_msgs'][] = array(
                'level' => 'success',
                'message' => sprintf(_('Move for GeoKret %s saved.'), gkid($this->geokretId)),
            );
        } else {
            $_SESSION['alert_msgs'][] = array(
                'level' => 'danger',
                'message' => sprintf(_('An error occured while saving log for GeoKret %s.'), gkid($this->geokretId)),
            );
        }

        return $dbIsSuccess;
    }

    public function insert() {
        $tripR = new TripRepository(\GKDB::getLink());

        if ($tripR->insertTripStep($this)) {
            $this->actualizeGeokret();

            return true;
        }

        return false;
    }

    public function update() {
        $tripR = new TripRepository(\GKDB::getLink());

        if ($tripR->updateTripStep($this)) {
            $this->actualizeGeokret();

            return true;
        }

        return false;
    }

    public function actualizeGeokret() {
        aktualizuj_droge($this->geokretId);     // update distance
        aktualizuj_skrzynki($this->geokretId);  // counts number of visited caches
        aktualizuj_ost_pozycja_id($this->geokretId);    // Last GeoKret position
        aktualizuj_ost_log_id($this->geokretId);        // Last log
        aktualizuj_obrazek_statystyki($this->geokret->ownerId); // compute pictures statistics
        aktualizuj_rekach($this->geokretId);    // compute current holder
        konkret_mapka($this->geokretId);        // generates a file with a map of the mole
        if ($this->logType->isCoordinatesRequired()) {
            aktualizuj_race($this->geokretId, $this->lat, $this->lon);
        }
        if ($_SESSION['isLoggedIn'] && $this->geokret->ownerId != $_SESSION['currentUser']) {
            aktualizuj_obrazek_statystyki($_SESSION['currentUser']);
        }
    }

    public function author() {
        $user = new User();
        $user->id = $this->userId;
        // Workaround: Fix database encoding
        $user->username = html_entity_decode($this->username);

        return $user;
    }

    public function enrichFields() {
        $this->logTypeString = $this->logType->getLogTypeString();
        $this->htmlContent = $this->getHtmlContent();
    }

    public function isEdit() {
        return !is_null($this->tripStep->ruchId);
    }

    public function isAuthor() {
        return $_SESSION['isLoggedIn'] && $_SESSION['currentUser'] === $this->userId || $_SESSION['isSuperUser'];
    }

    public function getHtmlContent() {
        if ($this->waypoint) {
            $linkTitle = _('link to the cache details');
            $htmlContent = <<<EOHTML
<b><a href="$this->waypointLink" title="$linkTitle">$this->waypoint</a> $this->waypointName</b> $this->waypointType $this->waypointOwner.<br/>
EOHTML;
        }
        $commentExtract = $this->comment;
        if (strlen($commentExtract) > 100) {
            $commentExtract = substr($commentExtract, 0, 100).'(...)';
        }
        $htmlContent .= <<<EOHTML
$this->ruchDataDodania $this->username ($this->logTypeString) $this->country ($this->distance km): $commentExtract
EOHTML;

        return $htmlContent;
    }

    public static function generate(int $geokretId, &$geokret, $logtype = LogType::LOG_TYPE_DIPPED) {
        $lipsum = new \joshtronic\LoremIpsum();
        $trip = new \Geokrety\Domain\TripStep();
        $trip->geokretId = $geokretId;
        $trip->geokret = $geokret;
        $trip->lat = 53.14598;
        $trip->lon = 23.18567;
        $trip->name = $lipsum->words(3);
        $trip->alt = 150;
        $trip->ruchId = random_int(424242, 888888);
        $trip->setDate((new \DateTime())->sub(new \DateInterval('P10DT8H42M23S')));
        $trip->setDateLogged(new \DateTime());
        $trip->waypoint = 'GC5BRQK';
        $trip->country = 'fr';
        $trip->userId = random_int(424242, 888888);
        $trip->username = $lipsum->words(1);
        $trip->setComment(_('Follow the white rabbit 🐇'));
        $trip->setLogtype($logtype);
        $trip->app = CONFIG_GK_APP_NAME;
        $trip->appVer = CONFIG_GK_VERSION;
        $trip->picturesCount = 0;
        $trip->commentsCount = 0;

        return $trip;
    }
}
