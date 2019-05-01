<?php

namespace Geokrety\Repository;

class MailRepository extends AbstractRepository {
    const SELECT_MAIL = <<<EOQUERY
SELECT    id_maila, random_string, `from`, `to`, temat, tresc, timestamp, ip
FROM      `gk-maile` ma
EOQUERY;

    public function getById($id) {
        $id = $this->validationService->ensureIntGTE('id', $id, 1);

        $where = <<<EOQUERY
  WHERE id_maila = ?
  LIMIT 1
EOQUERY;

        $sql = self::SELECT_MAIL.$where;
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
        $mail = new \Geokrety\Domain\Mail();

        $stmt->bind_result($mail->id, $mail->uuid, $mail->fromUserId,
                           $mail->toUserId, $mail->subject, $mail->message,
                           $mail->timestamp, $mail->ip);
        $stmt->fetch();
        $stmt->close();

        return $mail;
    }

    public function hasUserSentMessageInLast($userId, $rateInMinutes) {
        $userId = $this->validationService->ensureIntGTE('userId', $userId, 1);

        $where = <<<EOQUERY
  WHERE `from` = ?
  AND TIMESTAMPDIFF(MINUTE, timestamp, NOW()) < $rateInMinutes
  LIMIT 1
EOQUERY;

        $sql = self::SELECT_MAIL.$where;
        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('d', $userId)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();

        return $stmt->num_rows > 0 ? true : false;
    }

    public function insertMail(\Geokrety\Domain\Mail &$mail) {
        $sql = <<<EOQUERY
INSERT INTO `gk-maile`
            (random_string, `from`, `to`, temat, tresc, ip)
VALUES      (?, ?, ?, ?, ?, ?)
EOQUERY;

        $bind = array(
            $mail->uuid, $mail->fromUserId,
            $mail->toUserId, $mail->subject,
            $mail->message, $mail->ip,
        );

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param('siisss', ...$bind)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();

        if ($stmt->affected_rows >= 0) {
            return true;
        }

        danger(_('Failed to save mail…'));

        return false;
    }
}
