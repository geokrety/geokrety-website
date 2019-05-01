<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
loginFirst();

$TYTUL = _('Contact user').'';

$g_recaptcha = $_POST['g-recaptcha-response'];
$kret_temat = $_POST['subject'];
$kret_tresc = $_POST['message'];

$g_re = $_GET['re'];
$g_temat_maila = $_GET['temat_maila'];
$g_to = $_GET['to'];
$g_tresc_maila = $_GET['tresc_maila'];

//----------- FORM -------------- //

$smarty->assign('content_template', 'email.tpl');
$smarty->assign('GOOGLE_RECAPTCHA_PUBLIC_KEY', $GOOGLE_RECAPTCHA_PUBLIC_KEY);
$smarty->assign('javascript', 'https://www.google.com/recaptcha/api.js');

if (!ctype_digit($g_to)) {
    danger(_('Wrong recipient'));
}

$userR = new \Geokrety\Repository\UserRepository(GKDB::getLink());
$userFrom = $userR->getById($_SESSION['currentUser']);
$smarty->assign('userFrom', $userFrom);
$userTo = $userR->getById($g_to);
$smarty->assign('userTo', $userTo);

$mailR = new \Geokrety\Repository\MailRepository(GKDB::getLink());
$hasSentMailRecently = $mailR->hasUserSentMessageInLast($_SESSION['currentUser'], $config['mail_rate_limit']);

if (empty($userTo->email)) {
    danger(_('This user hasn\'t defined an email address yet.'));
    $userTo->redirect();
} elseif (!$userTo->acceptEmail) {
    danger(_('Sorry, this user choose to not receive any mail.'));
    $userTo->redirect();
} elseif ($userTo->isEmailActive != 0) {
    danger(_('Sorry, the recorded mail address fro this user is invalid.'));
    $userTo->redirect();
} elseif ($hasSentMailRecently) {
    warning(sprintf(_('You have already sent a mail recently. Please wait %d minutes between each message.'), $config['mail_rate_limit']));
} elseif (empty($userFrom->email)) {
    danger(_('You haven\'t defined an email address yet.'));
    $userTo->redirect();
} else {
    if (ctype_digit($g_re)) {
        $geokretR = new \Geokrety\Repository\KonkretRepository(GKDB::getLink());
        $geokret = $geokretR->getById($g_re);
        if (!is_null($geokret)) {
            $smarty->assign('subject', 'GeoKret: '.$geokret->name);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $resp = null;
        if ($GOOGLE_RECAPTCHA_PUBLIC_KEY) {
            $recaptcha = new \ReCaptcha\ReCaptcha($GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($g_recaptcha, getenv('HTTP_X_FORWARDED_FOR'));
        }

        $error = false;
        if (!empty($GOOGLE_RECAPTCHA_PUBLIC_KEY) && !$resp->isSuccess()) {
            danger(_('reCaptcha failed!'), $config['mail_rate_limit']);
            $error = true;
        }
        if ($hasSentMailRecently) {
            danger(sprintf(_('Sorry, but you have to wait %d minutes between each message.'), $config['mail_rate_limit']));
            $error = true;
        }

        if (!$error) {
            include_once 'random_string.php';

            $mail = new \Geokrety\Domain\Mail();
            $mail->uuid = random_string(10);
            $mail->fromUserId = $userFrom->id;
            $mail->toUserId = $userTo->id;
            $mail->subject = trim($kret_temat);
            $mail->message = trim($kret_tresc);
            $mail->ip = getenv('HTTP_X_FORWARDED_FOR');

            $mail->insert();

            // ----------- sending mail ------------- //

            $headers = 'MIME-Version: 1.0'."\r\n";
            $headers .= 'Content-Type: text/plain; charset=UTF-8'."\r\n";
            $headers .= 'From: Geokrety.org <'.$config['support_mail'].">\r\n";
            $headers .= "Reply-To: $userFrom->username <$userFrom->email>"."\r\n";

            $tresc = "This email was sent by user $userFrom->username (".$config['adres'].$userFrom->geturl().')
If you suspect an abuse, please let us know: '.$config['support_mail']."
Referer: $mail->uuid

--------------------------------------------------------------------------

".wordwrap($mail->message).'

--------------------------------------------------------------------------';

            mail($userTo->email, "[GeoKrety] [Contact] ($userFrom->username) $mail->subject", $tresc, $headers);
            success(_('Your message has been sent.'));
            $userTo->redirect();
        }
    }
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
