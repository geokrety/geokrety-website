<?php

require_once '__sentry.php';

// Main page of GeoKrety email2GK (beta)

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Email2GK :: β (beta)');

$TRESC = '<p>In some cases you would like to log your position in GK (eg writing GeoBlog from your holiday trip) and you don\'t have an access to WWW to enter coordinates on the geokrety.org. No internet cafe nearby, logging via mobile phone and GPRS can be really expensive... But here comes the solution. <strong>Email2GK gateway</strong> ;)</p>

<p>In many (all?) mobile networks you can send email via sms in your mobile phone. Sending sms is generally cheaper then internet connection. And the idea of this gateway is simple: You send email (via sms) to us (address in the picture below) and the system logs your entry as a normal move.</p>

<p><strong>The format</strong> of the email is:<br />
Subject: <i>tracking code</i><br />
Body: <i>dd mm.mmm#dd mm.mmm#text</i>
</p>

<p>An example. In <a href="http://www.era.pl/">Era</a> mobile network you can send email via sms by sending sms to +48602969696; the message format they expect is: <i>email-address#subject#body</i>. So let say that we would like to log entry of GK with tracking code V75w45 (2). Our lat is 52 01.25 (3), lon is 20 36.24 (4). So we prepare a message with a contents as in the picture below and send it to +48602969696.</p>

<p><img src="templates/email-komorka.jpg" alt="" width="339" height="481" /></p>

<p>The resulting logs you can see at the page of this geokret: <a href="'.$config['adres'].'konkret.php?id=1084">'.$config['adres'].'konkret.php?id=1084</a></p>

<p>Have fun!</p>

';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
