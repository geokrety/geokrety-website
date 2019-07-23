<?php

namespace Geokrety\Repository;

class TripCommentRepository extends AbstractRepository {
    const SELECT_COMMENT = <<<EOQUERY
SELECT    comment_id, ruch_id, kret_id, user_id, data_dodania, comment, type, user
FROM      `gk-ruchy-comments`
LEFT JOIN `gk-users` ON (user_id = userid)
EOQUERY;

    public function getByTripIds(array $ids) {
        $count = count($ids);
        if (!$count) {
            return array();
        }

        $where = <<<EOQUERY
  WHERE     ruch_id IN (%s)
  ORDER BY  ruch_id, comment_id ASC
EOQUERY;
        $where = sprintf($where, implode(',', array_fill(0, $count, '?')));

        $sql = self::SELECT_COMMENT.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param(str_repeat('d', $count), ...$ids)) {
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
        $stmt->bind_result($id, $tripId, $geokretId, $userId, $date, $comment, $type, $username);

        $comments = array();
        while ($stmt->fetch()) {
            $tripComment = new \Geokrety\Domain\TripComment();
            $tripComment->id = $id;
            $tripComment->tripId = $tripId;
            $tripComment->geokretId = $geokretId;
            $tripComment->userId = $userId;
            $tripComment->username = $username;
            $tripComment->setDate($date);
            $tripComment->comment = $comment;
            $tripComment->type = $type;

            $tripComment->enrichFields();
            array_push($comments, $tripComment);
        }

        $stmt->close();

        return $comments;
    }
}
