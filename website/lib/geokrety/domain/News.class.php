<?php

namespace Geokrety\Domain;

class News extends AbstractObject {
    public $id;
    public $date;
    public $title;
    public $content;
    public $authorName;
    public $authorId;
    public $commentsCount;
    public $lastCommentDate;

    public function author() {
        $user = new User();
        $user->id = $this->authorId;
        $user->username = $this->authorName;

        return $user;
    }

    public function isUserSubscribed($userId) {
    }

    public function getComments() {
        $newsCommentR = new \Geokrety\Repository\NewsCommentRepository(\GKDB::getLink());
        return $newsCommentR->getByNewsId($this->id);
    }
}
