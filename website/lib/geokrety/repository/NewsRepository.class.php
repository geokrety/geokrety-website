<?php

namespace Geokrety\Repository;

class NewsRepository extends AbstractRepository {
    const SELECT_NEWS = <<<EOQUERY
SELECT  news_id, DATE(date), tytul, tresc, who, userid, komentarze
FROM    `gk-news`
EOQUERY;

    public function getById($id) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $where = <<<EOQUERY
  WHERE     news_id = ?
EOQUERY;

        $sql = self::SELECT_NEWS.$where;
        list($news, $count) = $this->getBySql($sql, 'i', array($id));
        return $count > 0 ? $news[0] : null;
    }

    public function getRecent($start = 0, $limit = 2) {
        $start = $this->validationService->ensureIntGTE('start', $start, 0);
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
  ORDER BY  date DESC
  LIMIT     $start, $limit
EOQUERY;

        $sql = self::SELECT_NEWS.$where;
        return $this->getBySql($sql, '', array());
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
            return array(array(), 0);
        }

        // associate result vars
        $stmt->bind_result($id, $date, $title, $content, $authorName, $authorId, $commentsCount);

        $newsList = array();
        while ($stmt->fetch()) {
            $news = new \Geokrety\Domain\News();
            $news->id = $id;
            $news->date = $date;
            $news->title = $title;
            $news->content = $content;
            $news->authorName = $authorName;
            $news->authorId = $authorId;
            $news->commentsCount = $commentsCount;

            array_push($newsList, $news);
        }

        $stmt->close();

        return array($newsList, sizeof($newsList));
    }

    public function updateNewsCountAndLastCommentDate($id) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $sql = <<<EOQUERY
UPDATE      `gk-news`
SET         komentarze = (
                SELECT  COUNT(*)
                FROM    `gk-news-comments`
                WHERE   news_id = ?
            ),
            ostatni_komentarz = (
                SELECT  MAX(date)
                FROM    `gk-news-comments`
                WHERE   news_id = ?
            )
WHERE       news_id = ?
EOQUERY;

        $bind = array($id, $id, $id);
        $bindStr = 'iii';

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

    public function countTotalNews() {

        $sql = <<<EOQUERY
SELECT  COUNT(*)
FROM    `gk-news`
EOQUERY;

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
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count;
    }
}
