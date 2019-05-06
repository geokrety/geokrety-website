<?php

namespace Geokrety\Repository;

class PictureRepository {
    // database session opened with DBConnect();
    private $dblink;
    // report current activity to stdout
    private $verbose;

    // common validation service
    private $validationService;

    const SELECT_PICTURE = <<<EOQUERY
SELECT    ob.id, ob.typ as type, ob.id_kreta as gk_id, ob.user as user_id,
          ob.plik as filename, ob.opis as legend
FROM      `gk-obrazki` ob
EOQUERY;

    public function __construct($dblink, $verbose = false) {
        $this->dblink = $dblink;
        $this->verbose = $verbose;
        $this->validationService = new \Geokrety\Service\ValidationService();
    }

    public function getByGeokretAvatarId($id) {

        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $where = <<<EOQUERY
  WHERE obrazekid = ?
  LIMIT 1
EOQUERY;

        $sql = self::SELECT_PICTURE.$where;
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
        $stmt->bind_result($id, $type, $geokretId, $userId, $filename, $legend);

        $picture = new \Geokrety\Domain\Picture();
        while ($stmt->fetch()) {
            $picture->id = $id;
            $picture->type = $type;
            $picture->geokretId = $geokretId;
            $picture->userId = $userId;
            $picture->filename = $filename;
            $picture->legend = $legend;
        }

        $stmt->close();

        return $picture;
    }

    public function getByGeokretId($id) {

        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $where = <<<EOQUERY
  WHERE id_kreta = ?
  ORDER BY obrazekid DESC
EOQUERY;

        $sql = self::SELECT_PICTURE.$where;
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
        $stmt->bind_result($id, $type, $geokretId, $userId, $filename, $legend);

        $pictures = array();
        while ($stmt->fetch()) {
            $picture = new \Geokrety\Domain\Picture();
            $picture->id = $id;
            $picture->type = $type;
            $picture->geokretId = $geokretId;
            $picture->userId = $userId;
            $picture->filename = $filename;
            $picture->legend = $legend;

            array_push($pictures, $picture);
        }

        $stmt->close();

        return $pictures;
    }
}
