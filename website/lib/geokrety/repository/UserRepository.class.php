<?php

namespace Geokrety\Repository;

class UserRepository extends AbstractRepository {
    const SELECT_USER = <<<EOQUERY
SELECT    userid, user, email, email_invalid, joined, wysylacmaile, lang, lat, lon,
          promien, country, godzina, statpic, ostatni_mail, ostatni_login, secid
FROM      `gk-users` us
EOQUERY;

    public function getById($id) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $where = <<<EOQUERY
  WHERE userid = ?
  LIMIT 1
EOQUERY;

        $sql = self::SELECT_USER.$where;
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
            return null;
        }

        // associate result vars
        $stmt->bind_result($userid, $username, $email, $isEmailActive, $joinDate,
          $acceptEmail, $language, $latitude, $longitude, $observationRadius,
          $country, $emailHour, $statpic, $lastMail, $lastlogin, $secid);

        $user = new \Geokrety\Domain\User();
        while ($stmt->fetch()) {
            $user->id = $userid;
            $user->username = $username;
            $user->email = $email;
            $user->isEmailActive = $isEmailActive;
            $user->joinDate = $joinDate;
            $user->acceptEmail = $acceptEmail;
            $user->language = $language;
            $user->latitude = $latitude;
            $user->longitude = $longitude;
            $user->observationRadius = $observationRadius;
            $user->country = $country;
            $user->emailHour = $emailHour;
            $user->statpic = $statpic;
            $user->lastMail = $lastMail;
            $user->lastlogin = $lastlogin;
            $user->secid = $secid;
        }

        $stmt->close();

        return $user;
    }

    public function getOnlineUsers($interval = '5 MINUTE') {
        $sql = <<<EOQUERY
SELECT  userid, user
FROM    `gk-users`
WHERE   ostatni_login > DATE_SUB(NOW(), INTERVAL $interval)
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
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array();
        }

        // associate result vars
        $stmt->bind_result($userid, $username);

        $users = array();
        while ($stmt->fetch()) {
            $user = new \Geokrety\Domain\User();
            $user->id = $userid;
            $user->username = $username;

            array_push($users, $user);
        }
        $stmt->close();

        return $users;
    }

    public function updateUser(\Geokrety\Domain\User $user) {
        $bind = array(
            $user->username, $user->email, $user->isEmailActive,
            $user->acceptEmail, $user->language, $user->latitude,
            $user->longitude, $user->observationRadius,
            $user->country, $user->emailHour, $user->statpic,
            $user->lastMail, $user->lastlogin, $user->secid,
        );
        $bindStr = 'ssiisddisiisss';

        if (!empty($user->password)) {
            $set = ", haslo = '', haslo2 = ?";
            $bind[] = $user->password;
            $bindStr .= 's';
        // } else {
        //     $set = '';
        }

        $sql = <<<EOQUERY
UPDATE  `gk-users`
SET     user = ?, email = ?, email_invalid = ?, wysylacmaile = ?,
        lang = ?, lat = ?, lon = ?, promien = ?, country = ?,
        godzina = ?, statpic = ?, ostatni_mail = ?,
        ostatni_login = ?, secid = ?
        $set
WHERE   userid = $user->id
LIMIT   1
EOQUERY;

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

        danger(_('Failed to save user…'));

        return false;
    }

    public function loadUserPassword(\Geokrety\Domain\User $user) {
        $sql = <<<EOQUERY
SELECT  haslo, haslo2
FROM    `gk-users`
WHERE   userid = $user->id
LIMIT   1
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
        $nbRow = $stmt->num_rows;

        if ($nbRow == 0) {
            return array();
        }

        // associate result vars
        $stmt->bind_result($user->oldPassword, $user->password);
        $stmt->fetch();
        $stmt->close();

        return $user;
    }
}
