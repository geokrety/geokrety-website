<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\OwnerCode;
use GeoKrety\Model\Move;

class GeokretClaim extends Base {
    public function get(\Base $f3) {
        Smarty::render('pages/geokret_claim.tpl');
    }

    public function post(\Base $f3) {
        $ownerCode = new OwnerCode();
        $ownerCode->has('geokret', array('tracking_code = ?', $f3->get('POST.tc')));
        $ownerCode->load(array('token = ?', $f3->get('POST.oc')));

        if ($ownerCode->dry()) {
            \Flash::instance()->addMessage(_('Sorry, the provided owner code and tracking code doesn\'t match.'), 'danger');
            $this->get($f3);
            die();
        }

        if ($ownerCode->user) {
            \Flash::instance()->addMessage(_('Sorry, this owner code has already been used.'), 'danger');
            $this->get($f3);
            die();
        }

        $oldOwner = $ownerCode->geokret->owner;
        $f3->get('DB')->begin();
        // Register the transfer
        $ownerCode->user = $f3->get('SESSION.CURRENT_USER');
        $ownerCode->touch('claimed_on_datetime');
        $ownerCode->geokret->owner = $f3->get('SESSION.CURRENT_USER');

        // Create a move comment
        $move = new Move();
        $move->username = GK_BOT_USERNAME;
        $move->geokret = $ownerCode->geokret;
        $move->logtype = \GeoKrety\LogType::LOG_TYPE_COMMENT;
        $move->comment = sprintf('🙌 Owner change. From: [%s](%s) to: [%s](%s) /GK Team/', $oldOwner->username, $f3->alias('user_details', '@userid='.$oldOwner->id), $ownerCode->user->username, $f3->alias('user_details', '@userid='.$ownerCode->user->id));
        $move->app = GK_APP_NAME;
        $move->app_ver = GK_APP_VERSION;
        $move->touch('moved_on_datetime');

        if ($ownerCode->validate() && $ownerCode->geokret->validate() && $move->validate()) {
            $ownerCode->save();
            $ownerCode->geokret->save();
            $move->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Something went wrong while registering the adoption.'), 'danger');
                $f3->get('DB')->rollback();
            } else {
                \Flash::instance()->addMessage(sprintf(_('🎉 Congratulation! You are now the owner of %s.'), $ownerCode->geokret->name), 'success');
                $f3->get('DB')->commit();
                $this->sendEmail($ownerCode->geokret, $oldOwner);
                $context = array(
                    'oldUser' => $oldOwner,
                    'newUser' => $ownerCode->user,
                );
                \Event::instance()->emit('geokret.claimed', $ownerCode->geokret, $context);
                $f3->reroute('@geokret_details(@gkid='.$ownerCode->geokret->gkid.')');
            }
        }

        $this->get($f3);
    }

    protected function sendEmail($geokret, $user) {
        if (!$user->email) {
            return;
        }
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL);
        $smtp->set('To', $user->email);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', GK_EMAIL_SUBJECT_PREFIX.sprintf(_('Your GeoKret \'%s\' has been adopted 🎉'), $geokret->name));
        Smarty::assign('geokret', $geokret);
        Smarty::assign('user', $user);

        if (!$smtp->send(Smarty::fetch('mails/geokret_adopted.tpl'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the adoption mail notification to old owner.'), 'danger');
        }
    }
}
