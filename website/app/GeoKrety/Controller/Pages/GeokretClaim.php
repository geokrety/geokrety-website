<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\GeokretClaim as GeokretClaimEmail;
use GeoKrety\LogType;
use GeoKrety\Model\Move;
use GeoKrety\Model\OwnerCode;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\CurrentUserLoader;
use Sugar\Event;

class GeokretClaim extends Base {
    use CurrentUserLoader;

    public function get() {
        Smarty::render('pages/geokret_claim.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $ownerCode = new OwnerCode();
        $ownerCode->has('geokret', ['tracking_code = ?', $f3->get('POST.tc')]);
        $ownerCode->load(['token = ?', $f3->get('POST.oc')]);

        if ($ownerCode->dry()) {
            \Flash::instance()->addMessage(_('Sorry, the provided Owner Code and Tracking Code doesn\'t match.'), 'danger');
            $this->get();
            exit;
        }

        if ($ownerCode->geokret->isOwner()) {
            \Flash::instance()->addMessage(_('You are already the owner.'), 'danger');
            $this->get();
            exit;
        }

        if ($ownerCode->used !== OwnerCode::TOKEN_UNUSED) {
            \Flash::instance()->addMessage(_('Sorry, this owner code has already been used.'), 'danger');
            $this->get();
            exit;
        }

        $oldOwner = $ownerCode->geokret->owner;
        $f3->get('DB')->begin();
        // Register the transfer
        $ownerCode->adopter = $f3->get('SESSION.CURRENT_USER');
        $ownerCode->touch('claimed_on_datetime');
        $ownerCode->geokret->owner = $f3->get('SESSION.CURRENT_USER');
        $ownerCode->validating_ip = \Base::instance()->get('IP');
        $ownerCode->used = OwnerCode::TOKEN_USED;

        // Create a move comment
        $move = new Move();
        $move->username = GK_BOT_USERNAME;
        $move->geokret = $ownerCode->geokret;
        $move->move_type = LogType::LOG_TYPE_COMMENT;
        if (is_null($oldOwner)) {
            $move->comment = sprintf('🙌 Owner change. From: <em>no one</em> to: [%s](%s) /GK Team/', $ownerCode->adopter->username, $f3->alias('user_details', '@userid='.$ownerCode->adopter->id));
        } else {
            $move->comment = sprintf('🙌 Owner change. From: [%s](%s) to: [%s](%s) /GK Team/', $oldOwner->username, $f3->alias('user_details', '@userid='.$oldOwner->id), $ownerCode->adopter->username, $f3->alias('user_details', '@userid='.$ownerCode->adopter->id));
        }
        $move->app = GK_APP_NAME;
        $move->app_ver = GK_APP_VERSION;
        $move->touch('moved_on_datetime');

        if ($ownerCode->validate() && $ownerCode->geokret->validate() && $move->validate()) {
            $ownerCode->save();
            $ownerCode->geokret->save();
            // Reload OwnerCode so all linked objects are up-to-date
            $ownerCode->load(['id = ?', $ownerCode->id]);
            try {
                $move->save();
                $f3->get('DB')->commit();
                \Flash::instance()->addMessage(sprintf('🎉 '._('Congratulation! You are now the owner of %s.'), $ownerCode->geokret->name), 'success');
                $f3->reroute('@geokret_details(@gkid='.$ownerCode->geokret->gkid.')', false, false);
                if (!GK_DEVEL) {
                    $f3->abort(); // Send response to client now
                }

                $context = [
                    'oldUser' => $oldOwner,
                    'newUser' => $ownerCode->adopter,
                ];
                Event::instance()->emit('geokret.claimed', $ownerCode->geokret, $context);

                // Send email
                $smtp = new GeokretClaimEmail();
                $smtp->sendClaimedNotification($ownerCode->geokret, $oldOwner);

                return;
            } catch (\Exception $e) {
                $f3->get('DB')->rollback();
                \Flash::instance()->addMessage(_('Something went wrong while registering the adoption.'), 'danger');
            }
        }

        $this->get();
    }
}
