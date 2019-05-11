<?php

namespace Geokrety\Repository;

class KonkretRepository extends AbstractRepository {
    protected $count = <<<EOQUERY
SELECT  count(*) as total
FROM    `gk-geokrety` gk
EOQUERY;

    const SELECT_KONKRET = <<<EOQUERY
SELECT  id AS id, nr AS tracking_code, nazwa AS name, opis AS description,
        owner AS owner_id, data AS date_published,
        typ AS type, droga AS distance, skrzynki AS caches_count,
        zdjecia AS pictures_count, avatarid AS avatar_id,
        ost_pozycja_id AS last_position_id,
        ost_log_id AS last_log_id, hands_of AS holder_id, missing
FROM    `gk-geokrety` gk
EOQUERY;

    const SELECT_USER_KONKRET_INVENTORY = <<<EOQUERY
SELECT    gk.id, gk.nr, gk.nazwa, gk.opis, gk.data ,gk.typ, gk.droga, gk.skrzynki, gk.zdjecia, gk.owner, gk.missing,
          gk.ost_log_id, ru.data, ru.logtype, ru.koment, ru.user, us.user, ru.username,
          gk.ost_pozycja_id, ru2.waypoint, ru2.lat, ru2.lon, ru2.country, ru2.logtype, ru2.user,
          gk.avatarid, pic.plik,
          owner.user
FROM      `gk-geokrety` gk
LEFT JOIN `gk-ruchy` AS ru ON (gk.ost_log_id = ru.ruch_id)
LEFT JOIN `gk-ruchy` AS ru2 ON (gk.ost_pozycja_id = ru2.ruch_id)
LEFT JOIN `gk-users` AS us ON (ru.user = us.userid)
LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
LEFT JOIN `gk-users` AS owner ON (gk.owner = owner.userid)
EOQUERY;

    const SELECT_USER_KONKRET_WATCHED = <<<EOQUERY
SELECT    gk.id, gk.nr, gk.nazwa, gk.opis, gk.data ,gk.typ, gk.droga, gk.skrzynki, gk.zdjecia, gk.owner, gk.missing,
          gk.ost_log_id, ru.data, ru.logtype, ru.koment, ru.user, us.user, ru.username,
          gk.ost_pozycja_id, ru2.waypoint, ru2.lat, ru2.lon, ru2.country, ru2.logtype, ru2.user,
          gk.avatarid, pic.plik,
          owner.user
FROM (`gk-obserwable` ob)
LEFT JOIN `gk-geokrety` AS gk ON (ob.id = gk.id)
LEFT JOIN `gk-ruchy` AS ru ON (gk.ost_log_id = ru.ruch_id)
LEFT JOIN `gk-ruchy` AS ru2 ON (gk.ost_pozycja_id = ru2.ruch_id)
LEFT JOIN `gk-users` AS us ON (ru.user = us.userid)
LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
LEFT JOIN `gk-users` AS owner ON (gk.owner = owner.userid)
EOQUERY;

    public function getById($id) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $where = <<<EOQUERY
  WHERE id = ?
  LIMIT 1
EOQUERY;

        $sql = self::SELECT_KONKRET.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $id)) {
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
        $stmt->bind_result($id, $trackingCode, $name, $description,
                           $ownerId, $datePublished, $type,
                           $distance, $cachesCount, $picturesCount, $avatarId,
                           $lastPositionId, $lastLogId, $lastLogId, $missing);

        $geokret = new \Geokrety\Domain\Konkret();
        while ($stmt->fetch()) {
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->datePublished = $datePublished;
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $geokret->enrichFields();
        }

        $stmt->close();

        return $geokret;
    }

    public function getInventoryByUserId($id, $orderBy = null, $defaultWay = 'asc', $limit = 20, $curPage = 1) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);
        list($order, $way) = $this->validationService->ensureOrderBy('orderBy', $orderBy, ['id', 'owner', 'ru.data', 'droga', 'skrzynki'], $defaultWay);

        $total = self::count('WHERE gk.hands_of = ?', array('d', $id));
        $start = $this->paginate($total, $curPage, $limit);

        $orderDate = ($order == 'ru.data' ? 'if(ru.data <> \'\', 0, 1), ' : '');
        $where = <<<EOQUERY
    WHERE     gk.owner = ?
    ORDER BY  $orderDate $order $way, nazwa ASC
    LIMIT     $start, $limit
EOQUERY;

        $sql = self::SELECT_USER_KONKRET_INVENTORY.$where;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $id)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array(array(), $total);
        }

        // associate result vars
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename,
                           $ownerName);
        $geokrety = array();
        while ($stmt->fetch()) {
            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->datePublished = $datePublished;
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->ruchData = $lastLogDate;
            $lastLog->logType = $lastLogLogType;
            $lastLog->comment = $lastLogComment;
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->logType = $lastPositionLogType;
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return array($geokrety, $total);
    }

    public function getOwnedByUserId($id, $orderBy = null, $defaultWay = 'asc', $limit = 20, $curPage = 1) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);
        list($order, $way) = $this->validationService->ensureOrderBy('orderBy', $orderBy, ['id', 'waypoint', 'ru.data', 'droga', 'skrzynki'], $defaultWay);

        $total = self::count('WHERE gk.owner = ?', array('d', $id));
        $start = $this->paginate($total, $curPage, $limit);

        $orderDate = ($order == 'ru.data' ? 'if(ru.data <> \'\', 0, 1), ' : '');
        $where = <<<EOQUERY
    WHERE     gk.owner = ?
    ORDER BY  $orderDate $order $way, nazwa ASC
    LIMIT     $start, $limit
EOQUERY;
        $sql = self::SELECT_USER_KONKRET_INVENTORY.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $id)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array(array(), $total);
        }

        // associate result vars
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename,
                           $ownerName);
        $geokrety = array();
        while ($stmt->fetch()) {
            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->datePublished = $datePublished;
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->ruchData = $lastLogDate;
            $lastLog->logType = $lastLogLogType;
            $lastLog->comment = $lastLogComment;
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->logType = $lastPositionLogType;
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return array($geokrety, $total);
    }

    public function getWatchedByUserId($id, $orderBy = null, $defaultWay = 'asc', $limit = 20, $curPage = 1) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        list($order, $way) = $this->validationService->ensureOrderBy('orderBy', $orderBy, ['id', 'waypoint', 'ru.data', 'droga', 'skrzynki'], $defaultWay);

        $total = self::count('LEFT JOIN `gk-obserwable` AS ob ON (ob.id = gk.id) WHERE ob.userid = ?', array('d', $id));
        $start = $this->paginate($total, $curPage, $limit);

        $orderDate = ($order == 'ru.data' ? 'if(ru.data <> \'\', 0, 1), ' : '');
        $where = <<<EOQUERY
    WHERE     ob.userid = ?
    ORDER BY  $orderDate $order $way, nazwa ASC
    LIMIT     $start, $limit
EOQUERY;

        $sql = self::SELECT_USER_KONKRET_WATCHED.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $id)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array(array(), $total);
        }

        // associate result vars
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename,
                           $ownerName);
        $geokrety = array();
        while ($stmt->fetch()) {
            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->datePublished = $datePublished;
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->ruchData = $lastLogDate;
            $lastLog->logType = $lastLogLogType;
            $lastLog->comment = $lastLogComment;
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->logType = $lastPositionLogType;
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return array($geokrety, $total);
    }

    public function getRecentCreation($limit = 20) {
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
    ORDER BY  id DESC
    LIMIT     $limit
EOQUERY;

        $sql = self::SELECT_USER_KONKRET_INVENTORY.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
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
        $stmt->bind_result($id, $trackingCode, $name, $description, $datePublished, $type, $distance, $cachesCount, $picturesCount, $ownerId, $missing,
                           $lastLogId, $lastLogDate, $lastLogLogType, $lastLogComment, $lastLogUserId, $lastLogUsername, $lastLogUsername_,
                           $lastPositionId, $lastPositionWaypoint, $lastPositionLat, $lastPositionLon, $lastPositionCountry, $lastPositionLogType, $lastPositionUserId,
                           $avatarId, $avatarFilename,
                           $ownerName);
        $geokrety = array();
        while ($stmt->fetch()) {
            $geokret = new \Geokrety\Domain\Konkret();
            $geokret->id = $id;
            $geokret->trackingCode = $trackingCode;
            $geokret->name = $name;
            $geokret->description = $description;
            $geokret->ownerId = $ownerId;
            $geokret->ownerName = $ownerName;
            $geokret->datePublished = $datePublished;
            $geokret->type = $type;
            $geokret->distance = $distance; // road traveled in km
            $geokret->cachesCount = $cachesCount;
            $geokret->picturesCount = $picturesCount;
            $geokret->avatarId = $avatarId;
            $geokret->avatarFilename = $avatarFilename;
            $geokret->lastPositionId = $lastPositionId;
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $lastLog = new \Geokrety\Domain\TripStep($this->dblink);
            $lastLog->ruchId = $lastLogId;
            $lastLog->ruchData = $lastLogDate;
            $lastLog->logType = $lastLogLogType;
            $lastLog->comment = $lastLogComment;
            $lastLog->userId = $lastLogUserId;
            $lastLog->username = $lastLogUsername_ ? $lastLogUsername_ : $lastLogUsername;
            $geokret->lastLog = $lastLog;

            $lastPosition = new \Geokrety\Domain\TripStep($this->dblink);
            $lastPosition->ruchId = $lastLogId;
            $lastPosition->userId = $lastPositionUserId;
            $lastPosition->waypoint = $lastPositionWaypoint;
            $lastPosition->lat = $lastPositionLat;
            $lastPosition->lon = $lastPositionLon;
            $lastPosition->country = $lastPositionCountry;
            $lastPosition->logType = $lastPositionLogType;
            $geokret->lastPosition = $lastPosition;

            $geokret->enrichFields();
            array_push($geokrety, $geokret);
        }

        $stmt->close();

        return $geokrety;
    }

    public function getCountryTrack($gkId) {
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);

        $sql = <<<EOQUERY
SELECT    country, COUNT(*) as count
FROM      (SELECT @r := @r + (@country != country) AS gn,
                  @country := country AS sn,
                  s.*
           FROM   (SELECT @r := 0, @country := '') vars,
                   `gk-ruchy` as s
           WHERE id = ?
           AND s.lat is not null
           AND s.lon is not null
           ORDER BY data_dodania asc, data
          ) q
GROUP BY  gn
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
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array();
        }

        // associate result vars
        $stmt->bind_result($country, $count);

        $steps = array();
        while ($stmt->fetch()) {
            $step = new \Geokrety\Domain\CountryTrackStep();
            $step->country = $country;
            $step->count = $count;

            array_push($steps, $step);
        }

        $stmt->close();

        return $steps;
    }

    public function hasUserTouched($userId, $gkId) {
        if (is_null($userId)) {
            return false;
        }
        $userId = $this->validationService->ensureIntGTE('userid', $userId, 1);
        $gkId = $this->validationService->ensureIntGTE('gkid', $gkId, 1);

        $sql = <<<EOQUERY
SELECT  user FROM `gk-ruchy`
WHERE   id = ?
AND     user = ?
AND     logtype <> '2'
LIMIT   1
EOQUERY;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('dd', $gkId, $userId)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows;
    }

    public function getStatsByUserId($userId) {
        if (is_null($userId)) {
            return false;
        }
        $userId = $this->validationService->ensureIntGTE('userid', $userId, 1);

        $sql = <<<EOQUERY
SELECT COUNT(id), COALESCE(SUM(droga),0)
FROM `gk-geokrety`
WHERE owner = ?
AND typ != '2'
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
}
