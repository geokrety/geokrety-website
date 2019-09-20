<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class LocalMail extends Base {
    public function list($f3) {
        Smarty::render('pages/local_mails.tpl');
    }

    public function get($f3) {
        $mailid = $f3->get('PARAMS.mailid');
        $f3->set(sprintf('SESSION.LOCAL_MAIL.%d.read', $mailid), true);
        echo $f3->get(sprintf('SESSION.LOCAL_MAIL.%d.message', $mailid));
    }

    public function delete($f3) {
        $mailid = $f3->get('PARAMS.mailid');
        $f3->clear(sprintf('SESSION.LOCAL_MAIL.%d', $mailid));
        $f3->reroute('local_mail_list');
    }
}
