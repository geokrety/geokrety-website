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

    public function getUrl() {
        return sprintf('newscomments.php?newsid=%d', $this->id);
    }

    public function author() {
        $user = new User();
        $user->id = $this->authorId;
        // Workaround: Fix database encoding
        $user->username = html_entity_decode($this->authorName);

        return $user;
    }

    public function isUserSubscribed($userId) {
    }

    public function getComments() {
        $newsCommentR = new \Geokrety\Repository\NewsCommentRepository(\GKDB::getLink());

        return $newsCommentR->getByNewsId($this->id);
    }

    public function setDate($date, $format = 'Y-m-d') {
        if (is_a($date, '\Datetime')) {
            $this->date = $date;
        }
        $this->date = \DateTime::createFromFormat($format, $date, new \DateTimeZone('UTC'));
    }
}
