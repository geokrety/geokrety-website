<?php

namespace GeoKrety\Controller\Devel;

use GeoKrety\Controller\Base;
use GeoKrety\Service\Smarty;

class LocalMail extends Base {
    public function list(\Base $f3) {
        Smarty::render('devel/pages/local_mails.tpl');
    }

    public function get(\Base $f3) {
        $mailid = $f3->get('PARAMS.mailid');
        $f3->set(sprintf('SESSION.LOCAL_MAIL.%d.read', $mailid), true);
        // Not syntactically valid, but hopefully FF will understand it.
        // For the use case here, breaking the rules is not that important.
        echo '<link rel="stylesheet" href="/app-ui/css-mail/email.css">';
        echo $f3->get(sprintf('SESSION.LOCAL_MAIL.%d.message', $mailid));
    }

    public function delete(\Base $f3) {
        $mailid = $f3->get('PARAMS.mailid');
        $f3->clear(sprintf('SESSION.LOCAL_MAIL.%d', $mailid));
        $f3->reroute('@devel_mail_list');
    }

    public function delete_all(\Base $f3) {
        $f3->clear('SESSION.LOCAL_MAIL');
        $f3->reroute('@devel_mail_list');
    }

    public function delete_all_fast(\Base $f3) {
        $f3->clear('SESSION.LOCAL_MAIL');
        echo 'OK';
    }
}
