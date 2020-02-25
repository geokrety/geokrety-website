<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\GeokretClaim as GeokretClaimEmail;
use GeoKrety\Model\Move;
use GeoKrety\Model\OwnerCode;
use GeoKrety\Service\Smarty;

class GeokretClaim extends Base {
    public function get() {
        Smarty::render('pages/geokret_claim.tpl');
    }

    public function post(\Base $f3) {
        $ownerCode = new OwnerCode();
        $ownerCode->has('geokret', ['tracking_code = ?', $f3->get('POST.tc')]);
        $ownerCode->load(['token = ?', $f3->get('POST.oc')]);

        if ($ownerCode->dry()) {
            \Flash::instance()->addMessage(_('Sorry, the provided owner code and tracking code doesn\'t match.'), 'danger');
            $this->get();
            die();
        }

        if ($ownerCode->user) {
            \Flash::instance()->addMessage(_('Sorry, this owner code has already been used.'), 'danger');
            $this->get();
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

                $context = [
                    'oldUser' => $oldOwner,
                    'newUser' => $ownerCode->user,
                ];
                \Event::instance()->emit('geokret.claimed', $ownerCode->geokret, $context);

                // Send email
                $smtp = new GeokretClaimEmail();
                $smtp->sendClaimedNotification($ownerCode->geokret, $oldOwner);

                $f3->reroute('@geokret_details(@gkid='.$ownerCode->geokret->gkid.')');
            }
        }

        $this->get();
    }
}
