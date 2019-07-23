<?php

namespace Geokrety\Repository;

class NewsCommentRepository extends AbstractRepository {
    const SELECT_NEWS_COMMENT = <<<EOQUERY
SELECT  nc.comment_id, nc.news_id, nc.user_id, nc.date, nc.comment, nc.icon,
        us.user
FROM    `gk-news-comments` AS nc
join    `gk-users` AS us ON (nc.user_id = us.userid)
EOQUERY;

    public function getById($newsCommentId) {
        $newsCommentId = $this->validationService->ensureIntGTE('newsCommentId', $newsCommentId, 1);

        $where = <<<EOQUERY
  WHERE     comment_id = ?
  LIMIT     1
EOQUERY;

        $sql = self::SELECT_NEWS_COMMENT.$where;
        $newsCommentList = $this->getBySql($sql, 'i', array($newsCommentId));

        return sizeof($newsCommentList) > 0 ? $newsCommentList[0] : null;
    }

    public function getByNewsId($newsId) {
        $newsId = $this->validationService->ensureIntGTE('newsId', $newsId, 1);

        $where = <<<EOQUERY
  WHERE     news_id = ?
  ORDER BY  date DESC
EOQUERY;

        $sql = self::SELECT_NEWS_COMMENT.$where;

        return $this->getBySql($sql, 'i', array($newsId));
    }

    public function getBySql($sql, $bindStr, array $bind) {
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

        if ($nbRow == 0) {
            return array();
        }

        // associate result vars
        $stmt->bind_result($commentId, $newsId, $userId, $date, $comment, $icon,
                           $username);

        $newsCommentList = array();
        while ($stmt->fetch()) {
            $newsComment = new \Geokrety\Domain\NewsComment();
            $newsComment->id = $commentId;
            $newsComment->newsId = $newsId;
            $newsComment->userId = $userId;
            $newsComment->setDate($date);
            $newsComment->comment = $comment;
            $newsComment->icon = $icon;

            // Workaround: Fix database encoding
            $newsComment->comment = html_entity_decode($newsComment->comment);

            $user = new \Geokrety\Domain\User();
            $user->id = $userId;
            $user->username = $username;
            $newsComment->author = $user;

            // Workaround: Fix database encoding
            $user->username = html_entity_decode($user->username);

            array_push($newsCommentList, $newsComment);
        }

        $stmt->close();

        return $newsCommentList;
    }

    public function insertNewsComment(\Geokrety\Domain\NewsComment &$newsComment) {
        $sql = <<<EOQUERY
INSERT INTO `gk-news-comments`
            (news_id, user_id, date, comment, icon)
VALUES      (?, ?, NOW(), ?, ?)
EOQUERY;

        $bind = array(
            $newsComment->newsId, $newsComment->userId,
            $newsComment->comment, $newsComment->icon,
        );

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('iisi', ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();
        $newsComment->id = $stmt->insert_id;

        if ($stmt->affected_rows >= 0) {
            return true;
        }

        return false;
    }

    public function deleteNewsComment($newsCommentId) {
        $newsCommentId = $this->validationService->ensureIntGTE('newsCommentId', $newsCommentId, 1);

        $sql = <<<EOQUERY
DELETE FROM `gk-news-comments`
WHERE       comment_id = ?
EOQUERY;

        $bind = array(
            $newsCommentId,
        );
        $bindStr = 'i';

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param($bindStr, ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();

        if ($stmt->affected_rows >= 0) {
            return true;
        }

        return false;
    }
}
