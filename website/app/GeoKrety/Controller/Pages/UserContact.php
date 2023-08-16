<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\UserContact as EmailUserContact;
use GeoKrety\Model\Mail;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class UserContact extends Base {
    use \CurrentUserLoader;

    protected Mail $mail;

    protected function _beforeRoute(\Base $f3) {
        $mail = new Mail();
        $this->mail = $mail;
        $this->mail->from_user = $this->currentUser->id;
        Smarty::assign('mail', $this->mail);
    }

    protected function getPostUrl(\Base $f3) {
        return $f3->alias('mail_to_user');
    }

    protected function getPostRedirectUrl() {
        return sprintf('@user_details(@userid=%d)', $this->mail->to_user->id);
    }

    protected function loadToUser(\Base $f3) {
        if (!is_numeric($f3->get('PARAMS.userid'))) {
            $f3->error(404, _('This user does not exist.'));
        }
        $user = new User();
        $user->load(['id = ?', $f3->get('PARAMS.userid')]);
        if ($user->dry()) {
            $f3->error(404, _('This user does not exist.'));
        }
        $this->mail->to_user = $user;
    }

    protected function _get(\Base $f3) {
        Smarty::assign('postUrl', $this->getPostUrl($f3));
        $this->loadToUser($f3);
    }

    public function get(\Base $f3) {
        $this->_get($f3);
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_contact.tpl');
    }

    public function get_ajax(\Base $f3) {
        $this->_get($f3);
        Smarty::render('extends:base_modal.tpl|dialog/user_contact.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $this->loadToUser($f3);
        $mail = $this->mail;
        $mail->subject = $f3->get('POST.subject');
        $mail->content = $f3->get('POST.message');
        Smarty::assign('mail', $mail);

        // reCaptcha
        $this->checkCaptcha();

        if ($mail->validate()) {
            try {
                $mail->save();
            } catch (\Exception $e) {
                \Flash::instance()->addMessage(_('Failed to save the mail.'), 'danger');
                $this->get($f3);
                exit;
            }

            $mail->load(['_id = ?', $mail->getMapper()->get('_id')]);
            $smtp = new EmailUserContact();
            $smtp->sendUserMessage($mail);
            \Sugar\Event::instance()->emit('contact.created', $mail);
            \Flash::instance()->addMessage(sprintf(_('Your message to %s has been sent.'), $mail->to_user->username), 'success');
        }

        $f3->reroute($this->getPostRedirectUrl());
    }
}
