<?php

namespace Geokrety\Repository;

class KonkretRepository {
    // database session opened with DBConnect();
    private $dblink;
    // report current activity to stdout
    private $verbose;

    // common validation service
    private $validationService;

    const SELECT_KONKRET = <<<EOQUERY
SELECT  id AS id, nr AS tracking_code, nazwa AS name, opis AS description,
        owner AS owner_id, data AS date_published,
        typ AS type, droga AS distance, skrzynki AS caches_count,
        zdjecia AS pictures_count, avatarid AS avatar_id,
        ost_pozycja_id AS last_position_id,
        ost_log_id AS last_log_id, hands_of AS holder_id, missing
FROM    `gk-geokrety` gk
EOQUERY;

    public function __construct($dblink, $verbose = false) {
        $this->dblink = $dblink;
        $this->verbose = $verbose;
        $this->validationService = new \Geokrety\Service\ValidationService();
    }

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
            return NULL;
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
            $geokret->lastLogId = $lastLogId;
            $geokret->missing = $missing;

            $geokret->enrichFields();
        }

        $stmt->close();

        return $geokret;
    }
}
