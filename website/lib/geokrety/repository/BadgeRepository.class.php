<?php

namespace Geokrety\Repository;

class BadgeRepository extends AbstractRepository {
    const SELECT_BADGE = <<<EOQUERY
SELECT    id, userid, timestamp, `desc`, file
FROM      `gk-badges` ba
EOQUERY;

    public function getByUserId($userId) {
        $id = $this->validationService->ensureIntGTE('userId', $userId, 1);

        $where = <<<EOQUERY
  WHERE userid = ?
  Order BY timestamp ASC
EOQUERY;

        $sql = self::SELECT_BADGE.$where;
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
        $stmt->bind_result($id, $userId_, $attributionDate, $description, $filename);

        $badges = array();
        while ($stmt->fetch()) {
            $badge = new \Geokrety\Domain\Badge();
            $badge->id = $id;
            $badge->userId = $userId_;
            $badge->attributionDate = $attributionDate;
            $badge->description = $description;
            $badge->filename = $filename;

            array_push($badges, $badge);
        }

        $stmt->close();

        return $badges;
    }
}
