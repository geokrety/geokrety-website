<?php

namespace Geokrety\Domain;

class NewsComment extends AbstractObject {
    public $id;
    public $newsId;
    public $userId;
    public $date;
    public $comment;
    public $icon;

    public $author;

    public function insert() {
        $newsCommentR = new \Geokrety\Repository\NewsCommentRepository(\GKDB::getLink());

        return $newsCommentR->insertNewsComment($this);
    }

    public function delete() {
        $newsCommentR = new \Geokrety\Repository\NewsCommentRepository(\GKDB::getLink());

        return $newsCommentR->deleteNewsComment($this->id);
    }

    public function isAuthor() {
        return $_SESSION['isSuperUser'] || $_SESSION['isLoggedIn'] && $_SESSION['currentUser'] === $this->userId;
    }
}
