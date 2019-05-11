<?php

namespace Geokrety\Repository;

class NewsRepository extends AbstractRepository {

    const SELECT_NEWS = <<<EOQUERY
SELECT  news_id, DATE(date), tytul, tresc, who, userid, komentarze
FROM    `gk-news`
ORDER   BY date DESC
EOQUERY;

    public function get($limit=2) {
        $limit = $this->validationService->ensureIntGTE('limit', $limit, 1);

        $where = <<<EOQUERY
  LIMIT $limit
EOQUERY;

        $sql = self::SELECT_NEWS.$where;
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
            return array(array(), 0);
        }

        // associate result vars
        $stmt->bind_result($id, $date, $title, $content, $authorName, $authorId, $commentsCount);

        $newsList = array();
        while ($stmt->fetch()) {
            $news = new \Geokrety\Domain\News();
            $news->id = $userid;
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
}
