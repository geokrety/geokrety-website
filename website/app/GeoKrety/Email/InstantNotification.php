<?php

namespace GeoKrety\Email;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\GeokretLove;
use GeoKrety\Model\Move;
use GeoKrety\Model\MoveComment;
use GeoKrety\Model\User;

class InstantNotification extends BasePHPMailer {
    protected function allowSend(User $user): bool {
        return $user->isEmailValid();
    }

    public function __construct(?bool $exceptions = true) {
        parent::__construct($exceptions);
    }

    /**
     * Send instant notification for a new move.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendMoveNotification(User $user, Move $move) {
        // Prepare template data
        \GeoKrety\Service\Smarty::assign('move', $move);
        \GeoKrety\Service\Smarty::assign('geokret', $move->geokret);
        \GeoKrety\Service\Smarty::assign('author', $move->author);

        // Set subject with GeoKret name
        $this->setSubject(
            sprintf(_('New move for %s'), $move->geokret->name),
            '📍'
        );

        $this->setTo($user);
        $this->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        $unsubscribe_url = GK_SITE_BASE_SERVER_URL.\Base::instance()->alias('user_update_email_token', '@token='.$user->list_unsubscribe_token);
        $this->addCustomHeader('List-Unsubscribe', "<$unsubscribe_url>");

        if ($this->sendEmail('emails/instant-move-notification.tpl')) {
            $user->touch('last_mail_datetime');
            $user->save();

            return true;
        }

        return false;
    }

    /**
     * Send instant notification for a new comment on a move.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendCommentNotification(User $user, MoveComment $comment) {
        // Prepare template data
        \GeoKrety\Service\Smarty::assign('comment_id', $comment->id);
        \GeoKrety\Service\Smarty::assign('move', $comment->move);
        // \GeoKrety\Service\Smarty::assign('geokret', $comment->geokret);
        // \GeoKrety\Service\Smarty::assign('author', $comment->author);

        // Set subject with GeoKret name
        $this->setSubject(
            sprintf(_('New comment on %s'), $comment->geokret->name),
            '💬'
        );

        $this->setTo($user);
        $this->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        $unsubscribe_url = GK_SITE_BASE_SERVER_URL.\Base::instance()->alias('user_update_email_token', '@token='.$user->list_unsubscribe_token);
        $this->addCustomHeader('List-Unsubscribe', "<$unsubscribe_url>");

        if ($this->sendEmail('emails/instant-comment-notification.tpl')) {
            $user->touch('last_mail_datetime');
            $user->save();

            return true;
        }

        return false;
    }

    /**
     * Send instant notification for a new love on a GeoKret.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendLoveNotification(User $user, GeokretLove $love) {
        // Prepare template data
        \GeoKrety\Service\Smarty::assign('love', $love);
        \GeoKrety\Service\Smarty::assign('geokret', $love->geokret);
        \GeoKrety\Service\Smarty::assign('lover', $love->user);

        // Set subject with GeoKret name
        $this->setSubject(
            sprintf(_('Someone loved your %s! ❤️'), $love->geokret->name),
            '❤️'
        );

        $this->setTo($user);
        $this->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        $unsubscribe_url = GK_SITE_BASE_SERVER_URL.\Base::instance()->alias('user_update_email_token', '@token='.$user->list_unsubscribe_token);
        $this->addCustomHeader('List-Unsubscribe', "<$unsubscribe_url>");

        if ($this->sendEmail('emails/instant-love-notification.tpl')) {
            $user->touch('last_mail_datetime');
            $user->save();

            return true;
        }

        return false;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL, 'GeoKrety Instant Notifications');
    }
}
