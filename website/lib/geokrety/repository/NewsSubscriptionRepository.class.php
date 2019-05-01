<?php

namespace Geokrety\Repository;

class NewsSubscriptionRepository extends AbstractRepository {
    const SELECT_NEWS_SUBSCRIPTION = <<<EOQUERY
SELECT  news_id, user_id, `read`, post, subscribed
FROM    `gk-news-comments-access`
EOQUERY;

    public function getByNewsIdUserId($newsId, $userId) {
        $newsId = $this->validationService->ensureIntGTE('newsId', $newsId, 1);
        $userId = $this->validationService->ensureIntGTE('userId', $userId, 1);

        $where = <<<EOQUERY
  WHERE news_id = ?
  AND   user_id = ?
EOQUERY;

        $sql = self::SELECT_NEWS_SUBSCRIPTION.$where;
        $newsList = $this->getBySql($sql, 'ii', array($newsId, $userId));

        return sizeof($newsList) > 0 ? $newsList[0] : null;
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
        $stmt->bind_result($newsId, $userId, $read, $post, $subscribed);

        $newsSubscriptionList = array();
        while ($stmt->fetch()) {
            $newsSubscription = new \Geokrety\Domain\NewsSubscription();
            $newsSubscription->newsId = $newsId;
            $newsSubscription->userId = $userId;
            $newsSubscription->read = $read;
            $newsSubscription->post = $post;
            $newsSubscription->subscribed = $subscribed;

            array_push($newsSubscriptionList, $newsSubscription);
        }

        $stmt->close();

        return $newsSubscriptionList;
    }

    public function insertNewsSubscription(\Geokrety\Domain\NewsSubscription &$newsSubscription) {
        $sql = <<<EOQUERY
INSERT INTO `gk-news-comments-access`
            (news_id, user_id, `read`, post, subscribed)
VALUES      (?, ?, now(), ?, ?)
EOQUERY;

        $bind = array(
            $newsSubscription->newsId, $newsSubscription->userId,
            $newsSubscription->post,
            $newsSubscription->subscribed,
        );

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('iiss', ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();
        $newsSubscription->id = $stmt->insert_id;

        if ($stmt->affected_rows >= 0) {
            return true;
        }

        return false;
    }

    public function updateNewsSubscription(\Geokrety\Domain\NewsSubscription &$newsSubscription) {
        $sql = <<<EOQUERY
UPDATE      `gk-news-comments-access`
SET         news_id = ?, user_id = ?, `read` = now(), post = ?, subscribed = ?
WHERE       news_id = ?
AND         user_id = ?
EOQUERY;

        $bind = array(
            $newsSubscription->newsId, $newsSubscription->userId,
            $newsSubscription->post,
            $newsSubscription->subscribed,
            $newsSubscription->newsId, $newsSubscription->userId,
        );

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('iissii', ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();
        $newsSubscription->id = $stmt->insert_id;

        if ($stmt->affected_rows >= 0) {
            return true;
        }

        return false;
    }
}
