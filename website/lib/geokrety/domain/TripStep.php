<?php

namespace Geokrety\Domain;

class TripStep extends AbstractObject {
    public $lat; // latitude
    public $lon; // longitude
    public $alt; // altitude

    //~ ruchy centric
    public $ruchId; // ruchy entry id
    public $ruchData; // ruchy user provided date
    public $ruchDataDodania; // ruchy database added date

    public $userId; // ruchy author user id
    public $username; // ruchy author username
    public $comment; // ruchy username comment
    public $logType; // 0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip
    public $country; // country code
    public $droga; // road traveled in km

    //~ geokret centric
    public $geokretId; // geokret id
    public $geokret; // Konkret object

    public $app; // application name
    public $appVer; // application version
    public $picturesCount; // number of pictures
    public $commentsCount; // number of comments

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

    public function __construct($waypoint) {
        $this->waypoint = $waypoint;
    }

    public function author() {
        $user = new User();
        $user->id = $this->userId;
        $user->username = $this->username;

        return $user;
    }

    public function enrichFields() {
        $this->logTypeString = $this->getLogTypeString();
        $this->htmlContent = $this->getHtmlContent();
    }

    public function getLogTypeString() {
        switch ($this->logType) {
            case 0: return _('drop');
            case 1: return _('grap');
            case 2: return _('comment');
            case 3: return _('met');
            case 4: return _('archive');
            case 5: return _('dip');
            default: return null;
        }
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
}
