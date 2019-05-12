<?php

namespace Geokrety\Repository;

class PictureRepository extends AbstractRepository {

    const SELECT_PICTURE = <<<EOQUERY
SELECT    ob.id, ob.typ as type, ob.id_kreta as gk_id, ob.user as user_id,
          ob.plik as filename, ob.opis as legend
FROM      `gk-obrazki` ob
EOQUERY;

    const SELECT_PICTURES_DETAILS = <<<EOQUERY
SELECT    ob.id, ob.typ as type, ob.id_kreta as gk_id, ob.user as user_id,
          ob.plik as filename, ob.opis as legend, gk.nazwa as gk_name,
          us.user as username, ru.country, ru.data as date, ru.ruch_id
FROM      `gk-obrazki` ob
LEFT JOIN `gk-geokrety` gk ON (ob.id_kreta = gk.id)
LEFT JOIN `gk-users` us ON (ob.user = us.userid)
LEFT JOIN `gk-ruchy` ru ON (ob.id = ru.ruch_id )
EOQUERY;

    public function getAvatarByUserId($id) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $avatarType = 2;
        // $avatarType = \Geokrety\Domain\AVATAR; // TODO why this doesn't work???
        $where = <<<EOQUERY
  WHERE user = ?
  AND   typ = $avatarType
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
            return null;
        }

        // associate result vars
        $stmt->bind_result($id, $type, $geokretId, $userId, $filename, $legend);

        $picture = new \Geokrety\Domain\PictureUser();
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

    public function getAvatarByGeokretId($id) {
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
            return null;
        }

        // associate result vars
        $stmt->bind_result($id, $type, $geokretId, $userId, $filename, $legend);

        $picture = new \Geokrety\Domain\PictureGeoKret();
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
            return array();
        }

        // associate result vars
        $stmt->bind_result($id, $type, $geokretId, $userId, $filename, $legend);

        $pictures = array();
        while ($stmt->fetch()) {
            $picture = new \Geokrety\Domain\PictureGeoKret();
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

    public function getRecentPictures($limit) {
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
  ORDER BY  `obrazekid` DESC
  LIMIT     ?
EOQUERY;

        $sql = self::SELECT_PICTURES_DETAILS.$where;

        return $this->getPicturesSql($sql, array($limit));
    }

    public function getPictures($start, $limit = PICTURES_PER_GALLERY_PAGE) {
        $where = <<<EOQUERY
  ORDER BY  `obrazekid` DESC
  LIMIT     ?, ?
EOQUERY;

        $sql = self::SELECT_PICTURES_DETAILS.$where;

        return $this->getPicturesSql($sql, array($start, $limit));
    }

    public function getPicturesByAuthorId($userId, $start, $limit = PICTURES_PER_GALLERY_PAGE) {
        $where = <<<EOQUERY
  WHERE     ob.user = ?
  ORDER BY  obrazekid DESC
  LIMIT     ?, ?
EOQUERY;

        $sql = self::SELECT_PICTURES_DETAILS.$where;

        return $this->getPicturesSql($sql, array($userId, $start, $limit));
    }

    public function getPicturesByGkOwnerId($userId, $start, $limit = PICTURES_PER_GALLERY_PAGE) {
        $where = <<<EOQUERY
  WHERE     gk.owner = ?
  ORDER BY  obrazekid DESC
  LIMIT     ?, ?
EOQUERY;

        $sql = self::SELECT_PICTURES_DETAILS.$where;

        return $this->getPicturesSql($sql, array($userId, $start, $limit));
    }

    public function getPicturesSql($sql, array $params = null) {
        $count = count($params);

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if ($count && !$stmt->bind_param(str_repeat('d', $count), ...$params)) {
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
        $stmt->bind_result($id, $type, $geokretId, $userId, $filename, $legend,
                           $geokretName, $username, $country, $date, $tripId);

        $pictures = array();
        while ($stmt->fetch()) {
            $picture = null;
            switch ($type) {
                case 0:
                  $picture = new \Geokrety\Domain\PictureGeoKret();
                  $picture->geokretId = $geokretId;
                  $picture->geokretName = $geokretName;
                  $picture->country = $country;
                  $picture->date = $date;
                  break;
                case 1:
                  $picture = new \Geokrety\Domain\PictureTrip();
                  $picture->geokretId = $geokretId;
                  $picture->geokretName = $geokretName;
                  $picture->country = $country;
                  $picture->date = $date;
                  $picture->tripId = $tripId;
                  break;
                case 2:
                  $picture = new \Geokrety\Domain\PictureUser();
                  $picture->username = $username;
                  break;
                default:
                  throw new \Exception("picture id:$id has a wrong type ($type) : ($stmt->errno) ".$stmt->error);
            }
            $picture->id = $id;
            $picture->type = $type;
            $picture->userId = $userId;
            $picture->filename = $filename;
            $picture->legend = $legend;

            array_push($pictures, $picture);
        }

        $stmt->close();

        return $pictures;
    }

    public function countTotalPicturesBySql($sql, array $params = null) {
        $count = count($params);

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if ($count && !$stmt->bind_param(str_repeat('d', $count), ...$params)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

        return $total;
    }

    public function countTotalPictures() {
        $sql = <<<EOQUERY
SELECT    COUNT(*) as total
FROM      `gk-obrazki` ob
LIMIT     1
EOQUERY;

        return $this->countTotalPicturesBySql($sql);
    }

    public function countTotalPicturesByAuthorId($userId) {
        $userId = $this->validationService->ensureIntGTE('userId', $userId, 1);

        $sql = <<<EOQUERY
SELECT  COUNT(*) as total
FROM    `gk-obrazki`
WHERE   user = ?
LIMIT   1
EOQUERY;

        return $this->countTotalPicturesBySql($sql, array($userId));
    }

    public function countTotalPicturesByGkOwnerId($userId) {
        $userId = $this->validationService->ensureIntGTE('userId', $userId, 1);

        $sql = <<<EOQUERY
SELECT    COUNT(*) as total
FROM      `gk-obrazki` ob
LEFT JOIN `gk-geokrety` gk ON (ob.id_kreta = gk.id)
WHERE     gk.owner = ?
LIMIT     1
EOQUERY;

        return $this->countTotalPicturesBySql($sql, array($userId));
    }
}
