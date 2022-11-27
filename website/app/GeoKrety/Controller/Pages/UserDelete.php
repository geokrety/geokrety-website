<?php

namespace GeoKrety\Controller;

use CurrentUserLoader;
use Flash;
use GeoKrety\Service\Smarty;
use GeoKrety\Session;
use Sugar\Event;

class UserDelete extends Base {
    use CurrentUserLoader;

    public function post(\Base $f3) {
        $operation_result = array_sum($f3->get('SESSION.delete_account_operation_numbers'));
        if (is_null($operation_result) || $f3->get('POST.operation_result') != $operation_result) {
            Flash::instance()->addMessage(_('Wrong operation result.'), 'danger');
            $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id));
        }

        // reCaptcha
        $this->checkCaptcha();

        $f3->get('DB')->begin();
        $anonymize_comments = filter_var($f3->get('POST.removeCommentContentCheckbox'), FILTER_VALIDATE_BOOLEAN);
        $sql = 'SELECT delete_user(?, ?)'; // TODO: Could this be a database trigger?
        $result = $f3->get('DB')->exec($sql, [$this->currentUser->id, $anonymize_comments]);
        // TODO: Archive user's GeoKrety

        if ($result === false) {
            $f3->get('DB')->rollback();
            Flash::instance()->addMessage(_('Something went wrong. If the problem persists, please contact us.'), 'danger');
            $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id));
        }

        $f3->get('DB')->commit();

        Event::instance()->emit('user.deleted', $this->currentUser, $context);
        Login::disconnectUser($f3);
        Session::closeAllSessionsForUser($this->currentUser);
        Flash::instance()->addMessage(_('Your account is now deleted. Thanks for playing with us.'), 'success');
        $f3->reroute('@home');
    }

    public function _get(\Base $f3) {
        $number1 = rand(0, 9);
        $number2 = rand(0, 9);
        if (GK_DEVEL) {
            $number1 = $number2 = 1;
        }
        $f3->set('SESSION.delete_account_operation_numbers', [$number1, $number2]);
        Smarty::assign('number1', $number1);
        Smarty::assign('number2', $number2);
    }

    public function get(\Base $f3) {
        $this->_get($f3);
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_delete_account.tpl');
    }

    public function get_ajax(\Base $f3) {
        $this->_get($f3);
        Smarty::render('extends:base_modal.tpl|dialog/user_delete_account.tpl');
    }
}
