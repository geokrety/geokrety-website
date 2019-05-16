<?php

namespace Geokrety\Domain;

class Mail extends AbstractObject {
    public $id;
    public $uuid;
    public $fromUserId;
    public $toUserId;
    public $subject;
    public $message;
    public $timestamp;
    public $ip;

    function insert() {
        $mailR = new \Geokrety\Repository\MailRepository(\GKDB::getLink());

        return $mailR->insertMail($this);
    }
}
