<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$validationService = new \Geokrety\Service\ValidationService();

$TYTUL = _('Register a new user');

require_once 'defektoskop.php';

if ($longin_status['plain'] != null) {
    $TRESC = defektoskop("<a href='longin.php?logout=1'>Logout</a>", false);
    include_once 'smarty.php';
    exit;
}

$g_recaptcha = $_POST['g-recaptcha-response'];
$kret_email = $_POST['inputEmail'];
$kret_haslo1 = $_POST['inputPassword'];
$kret_haslo2 = $_POST['inputPasswordConfirm'];
$language = $_POST['language'];
$kret_login = $_POST['inputUsername'];
$kret_wysylacmaile = $_POST['dailymail'];

$smarty->assign('GOOGLE_RECAPTCHA_PUBLIC_KEY', $GOOGLE_RECAPTCHA_PUBLIC_KEY);
$smarty->assign('javascript', 'https://www.google.com/recaptcha/api.js');
$smarty->assign('strengthify', CDN_ZXCVBN_JS); // Async loaded by strengthify
$smarty->append('javascript', CDN_STRENGTHIFY_JS);
$smarty->append('css', CDN_STRENGTHIFY_CSS);
$smarty->append('javascript', CDN_JQUERY_VALIDATION_JS);
$smarty->append('js_template', 'js/user_create.tpl.js');
$smarty->assign('content_template', 'useradd.tpl');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = false;
    $resp = null;
    if ($GOOGLE_RECAPTCHA_PUBLIC_KEY) {
        $recaptcha = new \ReCaptcha\ReCaptcha($GOOGLE_RECAPTCHA_SECRET_KEY);
        $resp = $recaptcha->verify($g_recaptcha, getenv('HTTP_X_FORWARDED_FOR'));
        if (!$resp->isSuccess()) {
            danger(_('reCaptcha failed!'));
        $error = 1;
        }
    }

    if ($error < 1) {
        if ((empty($kret_haslo1))) {
            danger(_('Password missing'));
            $error = 1;
        }
        if ((empty($kret_haslo2))) {
            danger(_('Password confirmation missing'));
            $error = 1;
        }
        if (($kret_haslo1 != $kret_haslo2) or (empty($kret_haslo1))) {
            danger(_('Passwords are different or empty!'));
            $error = 1;
        }
        if (strlen($kret_haslo1) < 5) {
            danger(_('Password to short (should be >= 5 characters)!'));
            $error = 1;
        }
        if (!filter_var($kret_email, FILTER_VALIDATE_EMAIL)) {
            danger(_('Invalid email address'));
            $error = 1;
        }

        include_once 'fn_haslo.php';

        $login = $validationService->noHtml($kret_login);
        $haslo2 = haslo_koduj($kret_haslo1);

        $userR = new \Geokrety\Repository\UserRepository(\GKDB::getLink());
        $user = $userR->getByUsername($login);

        if (!is_null($user)) {
            if (empty($user->email) && $user->joinDate < 3600 && $user->lastlogin == 0 && $user->ip == $_SERVER['HTTP_X_FORWARDED_FOR']) {
                warning(sprintf(_('It seems that <a href="mypage.php?userid=%s">your account</a> has already been created.<br />In order to confirm your email address, a link was sent to you. You have to clic the link to get a fully operational account. Until then, you will not be able to receive emails with daily summaries of moves of your GeoKrety. The link is valid for 5 days. Now you can perform operations on GeoKrety. Feel free to log in and enjoy GeoKrety.org! :)'), $user->id));
                header('Location: /longin.php');
                die();
            }
            danger(_('The username you entered is already in use.'));
            $error = 1;
        }

        $userEmail = $userR->getByEmail($kret_email);
        if (!is_null($userEmail)) {
            danger(_('The email you entered is already in use.'));
            $error = 1;
        }

        include_once 'verify_mail.php';
        if (!verify_email_address($kret_email)) {
            danger(_('Wrong email address?'));
            $error = 1;
        }
    }

    // ------------------------ if there are no errors ----------------
    if ($error < 1) {

        $kret_wysylacmaile = $kret_wysylacmaile == 'on' ? 1 : 0;

        $ip = getenv('HTTP_X_FORWARDED_FOR');
        $jezyk = (substr($language, 0, 2));
        $godzina_wysylki = rand(0, 23);

        include_once 'fn-generate_secid.php';
        $secid = generateRandomString(128);

        $user = new \Geokrety\Domain\User();
        $user->username = htmlentities($login);
        $user->password = $haslo2;
        $user->acceptEmail = $kret_wysylacmaile;
        $user->language = $jezyk;
        $user->emailHour = $godzina_wysylki;
        $user->secid = $secid;
        $user->ip = $ip;
        $user->email = '';
        $user->isEmailActive = 0;
        $user->observationRadius = 0;

        if (!$user->insert()) {
            danger(_('Failed to register user. Please repeat your registration at a later time.'));
            include_once 'smarty.php';
            die();
        }

        verify_mail_send($kret_email, $user->id);         // send email with a verification code

        success(_('Account successfully created. An email to confirm your email address was sent to you. When you confirm, your account will be fully operational. Until then, you will not be able to receive emails with daily summaries of moves of your GeoKrety. The link is valid for 5 days. Now you can perform operations on GeoKrety. Feel free to log in and enjoy GeoKrety.org! :) <strong>But please retain that without a confirmed email address, you will not be able to recover your accound in case of password loss</strong>.'));

        include_once 'aktualizuj.php';
        aktualizuj_obrazek_statystyki($user->id);

        errory_add("New user: $login id: $user->id", 1, 'NewUser');

        header('Location: /longin.php');
        die();
    }
}

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
