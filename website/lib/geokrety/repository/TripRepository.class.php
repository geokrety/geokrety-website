<?php

namespace Geokrety\Repository;

class TripRepository extends AbstractRepository {
    protected $count = <<<EOQUERY
SELECT  count(*) as total
FROM    `gk-ruchy` ru
EOQUERY;

    const SELECT_RUCHY = <<<EOQUERY
SELECT    ru.ruch_id, ru.id, ru.lat, ru.lon, ru.country, ru.alt, ru.waypoint, ru.data, ru.data_dodania,
          ru.user, ru.username, us.user, ru.koment, ru.logtype, ru.droga,
          wp.name, wp.typ, wp.owner, wp.status, wp.link,
          ru.app, ru.app_ver, ru.zdjecia, ru.komentarze,
          gk.nazwa, gk.nr, gk.opis, gk.owner, gk.data, gk.droga, gk.skrzynki, gk.zdjecia,
          gk.ost_pozycja_id, gk.ost_log_id, gk.hands_of, gk.missing, gk.typ, gk.avatarid,
          ob.plik, ob.opis
FROM      `gk-ruchy` AS ru
LEFT JOIN `gk-users` AS us ON ru.user = us.userid
LEFT JOIN `gk-waypointy` AS wp ON ru.waypoint = wp.waypoint
LEFT JOIN `gk-geokrety` AS gk ON ru.id = gk.id
LEFT JOIN `gk-obrazki` AS ob ON (gk.avatarid = ob.obrazekid AND ob.typ = '0')
EOQUERY;

    const SELECT_RECENT_RUCHY = <<<EOQUERY
SELECT      ru.ruch_id, ru.id, ru.lat, ru.lon, ru.country, ru.alt, ru.waypoint, ru.data, ru.data_dodania,
            ru.user, ru.username, us.user, ru.koment, ru.logtype, ru.droga,
            wp.name, wp.typ, wp.owner, wp.status, wp.link,
            ru.app, ru.app_ver, ru.zdjecia, ru.komentarze,
            gk.nazwa, gk.nr, gk.opis, gk.owner, gk.data, gk.droga, gk.skrzynki, gk.zdjecia,
            gk.ost_pozycja_id, gk.ost_log_id, gk.hands_of, gk.missing, gk.typ, gk.avatarid,
            ob.plik, ob.opis
FROM        (SELECT * FROM `gk-ruchy` r1 ORDER BY r1.ruch_id DESC LIMIT 50) ru
INNER JOIN  `gk-users` us ON (ru.user = us.userid)
LEFT JOIN   `gk-waypointy` AS wp ON ru.waypoint = wp.waypoint
INNER JOIN  `gk-geokrety` gk ON (ru.id = gk.id)
LEFT JOIN   `gk-obrazki` AS ob ON (gk.avatarid = ob.obrazekid)
EOQUERY;

    public function getByGeokretyId($geokretId, $limit, $noRecurs = false) {
        $action = 'Trip::getByGeokretyId';

        $geokretId = $this->validationService->ensureIntGTE('geokretyId', $geokretId, 1);
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
    WHERE       gk.id = ?
    AND         (logtype = '0' OR logtype = '3' OR logtype = '5')
    ORDER BY    ru.data DESC , ru.data_dodania DESC
    LIMIT       $limit
EOQUERY;

        $sql = self::SELECT_RUCHY.$where;

        return $this->getBySql($sql, 'd', array($geokretId), $noRecurs);
    }

    public function getByTripId($tripId) {
        $action = 'Trip::getByGeokretyId';

        $tripId = $this->validationService->ensureIntGTE('tripId', $tripId, 1);

        $where = <<<EOQUERY
    WHERE       `ruch_id` = ?
    LIMIT       1
EOQUERY;

        $sql = self::SELECT_RUCHY.$where;
        $tripSteps = $this->getBySql($sql, 'd', array($tripId));
        if (sizeof($tripSteps) > 0) {
            return $tripSteps[0];
        }

        return null;
    }

    public function getBySql($sql, $bindStr, array $bind, $noRecurs = false) {
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (sizeof($bind) && !$stmt->bind_param($bindStr, ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;
        $tripSteps = array();

        if ($nbRow == 0) {
            return $tripSteps;
        }

        // associate result vars
        $stmt->bind_result($ruchId, $geokretId, $lat, $lon, $country, $alt, $waypoint,
                           $data, $dataDodania, $user, $usernameAnonymous, $username, $koment, $logtype, $droga,
                           $wpName, $wpType, $wpOwner, $wpStatus, $wpLink,
                           $app, $appVer, $picturesCount, $commentsCount,
                           $gkName, $gkTrackingCode, $gkDescription, $gkOwnerId, $gkDatePublished, $gkDistance, $gkCachesCount, $gkPicturesCount,
                           $gkLastPositionId, $gkLastLogId, $gkHolderId, $gkMissing, $gkType, $gkAvatarId,
                           $picFilename, $picCaption
                           );

        while ($stmt->fetch()) {
            $trip = new \Geokrety\Domain\TripStep($waypoint);
            $trip->lat = $lat;
            $trip->lon = $lon;
            $trip->alt = $alt;
            $trip->ruchId = $ruchId;
            $trip->setDate($data);
            $trip->ruchDataDodania = $dataDodania;
            $trip->userId = $user;
            $trip->username = isset($username) ? $username : $usernameAnonymous;
            $trip->comment = $koment;
            $trip->setLogtype($logtype);
            $trip->country = $country;
            $trip->distance = $droga; // road traveled in km
            $trip->geokretId = $geokretId;
            $trip->geokretNr = $gkTrackingCode;
            $trip->app = $app;
            $trip->appVer = $appVer;
            $trip->picturesCount = $picturesCount;
            $trip->commentsCount = $commentsCount;

            $trip->waypoint = $waypoint;
            $trip->waypointName = $wpName;
            $trip->waypointType = $wpType;
            $trip->waypointOwner = $wpOwner;
            $trip->waypointStatus = $wpStatus;
            $trip->waypointLink = $wpLink;

            // Workaround: Fix database encoding
            $trip->comment = html_entity_decode($trip->comment);

            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $geokretId;
            $geokret->trackingCode = $gkTrackingCode;
            $geokret->name = $gkName;
            $geokret->description = $gkDescription;
            $geokret->ownerId = $gkOwnerId;
            $geokret->setDatePublished($gkDatePublished);
            $geokret->type = $gkType;
            $geokret->distance = $gkDistance; // road traveled in km
            $geokret->cachesCount = $gkCachesCount;
            $geokret->picturesCount = $gkPicturesCount;
            $geokret->avatarId = $gkAvatarId;
            $geokret->avatarFilename = $picFilename;
            $geokret->lastPositionId = $gkLastPositionId;
            $geokret->lastLogId = $gkLastLogId;
            if (!$noRecurs) {
                $geokret->lastLog = $trip;
            }
            $geokret->missing = $gkMissing;
            $geokret->holderId = $gkHolderId;
            $trip->geokret = $geokret;

            // Workaround: Fix database encoding
            $geokret->name = html_entity_decode($geokret->name);
            $geokret->description = html_entity_decode($geokret->description);

            $trip->enrichFields();
            array_push($tripSteps, $trip);
        }

        $stmt->close();

        return $tripSteps;
    }

    public function countTotalMoveByGeokretId($gkId) {
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);

        $sql = <<<EOQUERY
SELECT  COUNT(*)
FROM    `gk-ruchy` AS ru
WHERE   id = ?
LIMIT   1
EOQUERY;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $gkId)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count;
    }

    public function hasCurrentUserSeenGeokretId($gkId) {
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);

        $sql = <<<EOQUERY
SELECT  user
FROM    `gk-ruchy` AS ru
WHERE   id = ?
AND     user = ?
LIMIT   1
EOQUERY;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        $currentUser = $_SESSION['currentUser'];
        if (!$stmt->bind_param('dd', $gkId, $currentUser)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count;
    }

    public function checkSameEntryExists($gkId, $date, $comment) {
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);
        $date = $this->validationService->ensureDateTime('date', $date);

        $where = <<<EOQUERY
    WHERE       ru.id = ?
    AND         ru.data = ?
    AND         ru.koment = ?
EOQUERY;

        $sql = self::SELECT_RUCHY.$where;

        return $this->getBySql($sql, 'dss', array($gkId, $date->format('Y-m-d H:i:s'), $comment));
    }

    public function checkSameDateTimeExists($gkId, $date) {
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);
        $date = $this->validationService->ensureDateTime('date', $date);

        $where = <<<EOQUERY
    WHERE       ru.id = ?
    AND         ru.data = ?
EOQUERY;

        $sql = self::SELECT_RUCHY.$where;

        return $this->getBySql($sql, 'ds', array($gkId, $date->format('Y-m-d H:i:s')));
    }

    public function getAllTripByGeokretyId($geokretId, $start, $limit = 20) {
        $action = 'Trip::getAllTripByGeokretyId';

        $geokretId = $this->validationService->ensureIntGTE('geokretyId', $geokretId, 1);
        $start = $this->validationService->ensureIntGTE('start', $start, 0);
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
    WHERE ru.id = ?
    ORDER BY ru.data DESC
    LIMIT $start, $limit
EOQUERY;

        $sql = self::SELECT_RUCHY.$where;

        return $this->getBySql($sql, 'd', array($geokretId));
    }

    public function getAllTripByAuthorId($id, $orderBy = null, $defaultWay = 'desc', $limit = 20, $curPage = 1) {
        $action = 'Trip::getAllTripByAuthorId';

        $id = $this->validationService->ensureIntGTE('id', $id, 1);
        list($order, $way) = $this->validationService->ensureOrderBy('orderBy', $orderBy, ['ru.data', 'id', 'ru.waypoint', 'ru.droga'], $defaultWay);

        $total = self::count('WHERE ru.user = ?', array('d', $id));
        $start = $this->paginate($total, $curPage, $limit);

        $where = <<<EOQUERY
    WHERE ru.user = ?
    ORDER BY $order $way
    LIMIT $start, $limit
EOQUERY;

        $sql = self::SELECT_RUCHY.$where;
        $tripSteps = $this->getBySql($sql, 'd', array($id));

        return array($tripSteps, $total);
    }

    public function getAllTripByOwnerId($id, $orderBy = null, $defaultWay = 'desc', $limit = 20, $curPage = 1) {
        $action = 'Trip::getAllTripByOwnerId';

        $id = $this->validationService->ensureIntGTE('id', $id, 1);
        list($order, $way) = $this->validationService->ensureOrderBy('orderBy', $orderBy, ['ru.data', 'id', 'ru.waypoint', 'droga'], $defaultWay);

        $total = self::count('LEFT JOIN `gk-geokrety` AS gk ON (ru.id = gk.id) WHERE gk.owner = ?', array('d', $id));
        $start = $this->paginate($total, $curPage, $limit);

        $where = <<<EOQUERY
    WHERE gk.owner = ?
    ORDER BY $order $way
    LIMIT $start, $limit
EOQUERY;

        $sql = self::SELECT_RUCHY.$where;
        $tripSteps = $this->getBySql($sql, 'd', array($id));

        return array($tripSteps, $total);
    }

    public function getRecentTrip($limit = 10) {
        $action = 'Trip::getRecentTrip';
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
    WHERE       gk.typ <> '2'
    ORDER BY    ru.ruch_id DESC
    LIMIT       $limit
EOQUERY;

        $sql = self::SELECT_RECENT_RUCHY.$where;

        return $this->getBySql($sql, '', array());
    }

    public function getStatsByOwnerId($userId) {
        if (is_null($userId)) {
            return false;
        }
        $userId = $this->validationService->ensureIntGTE('userid', $userId, 1);

        $sql = <<<EOQUERY
SELECT  COUNT(ruch_id), COALESCE(SUM(droga),0)
FROM    `gk-ruchy` AS ru
WHERE   (logtype = '0' OR logtype = '5')
AND     user = ?
AND     ru.id IN (SELECT  id
                          FROM    `gk-geokrety`
                          WHERE   typ != '2'
                          )
LIMIT 1
EOQUERY;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $userId)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array();
        }

        // associate result vars
        $stmt->bind_result($count, $distance);
        $stmt->fetch();
        $stmt->close();

        return array(
            'count' => $count,
            'distance' => $distance,
        );
    }

    public function insertTripStep(\Geokrety\Domain\TripStep &$trip) {
        $sql = <<<EOQUERY
INSERT INTO `gk-ruchy`
            (id, lat, lon, country, alt, waypoint, data,
            user, username, koment, logtype,
            droga, app, app_ver, zdjecia, komentarze)
VALUES      (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
EOQUERY;

        $bind = array(
            $trip->geokretId, $trip->getLat(), $trip->getLon(),
            $trip->country, $trip->alt, $trip->getWaypoint(),
            $trip->getDate(), $trip->userId,
            $trip->username, $trip->comment, $trip->logType->getLogTypeId(),
            $trip->distance, $trip->app, $trip->appVer, $trip->picturesCount,
            $trip->commentsCount,
        );

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('iddsississsissii', ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();
        $trip->id = $stmt->insert_id;

        if ($stmt->affected_rows >= 0) {
            return true;
        }

        danger(_('Failed to create Trip Step…'));

        return false;
    }

    public function updateTripStep(\Geokrety\Domain\TripStep &$trip) {
        $sql = <<<EOQUERY
UPDATE  `gk-ruchy`
SET     id = ?, lat = ?, lon = ?, country = ?, alt = ?, waypoint = ?, data = ?,
        data_dodania = now(), user = ?, username = ?, koment = ?, logtype = ?,
        droga = ?, app = ?, app_ver = ?, zdjecia = ?, komentarze = ?
WHERE   ruch_id = ?
LIMIT   1
EOQUERY;

        $bind = array(
            $trip->geokretId, $trip->getLat(), $trip->getLon(),
            $trip->country, $trip->alt, $trip->getWaypoint(),
            $trip->getDate(), $trip->userId,
            $trip->username, $trip->comment, $trip->logType->getLogTypeId(),
            $trip->distance, $trip->app, $trip->appVer, $trip->picturesCount,
            $trip->commentsCount,
            $trip->ruchId,
        );

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('iddsississsissiii', ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();

        if ($stmt->affected_rows >= 0) {
            return true;
        }

        danger(_('Failed to update Trip Step…'));

        return false;
    }
}
