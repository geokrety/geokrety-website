<?php

require_once '__sentry.php';

// Main page of GeoKrety śćńółżźóółłż

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 3800; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Hall of fame');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

$kumy = '<a href="/mypage.php?userid=26422">kumy</a>';
$lineflyer = '<a href="/mypage.php?userid=17135">Lineflyer</a>';
$filips = '<a href="/mypage.php?userid=1">filips</a>';
$harrieklomp = '<a href="/mypage.php?userid=7861">Harrie Klomp</a>';

$TRESC = '
<p><i>Idę sobie i patrzę, a_leją zasłużonych... B. Smoleń</i></p>

<div class="rozdzial">'._('Developers').'</div>
	<ul>
	<li><strong>'.$filips.'</strong> idea, code and design</li>
	<li><strong><a href="/mypage.php?userid=6262">simor</a></strong> visions into code ;)</li>
	<li><strong>'.$kumy.'</strong> hosting, code and <a href="https://github.com/geokrety">planning GK v2</a></li>
	<li><strong><a href="/mypage.php?userid=35313">BSLLM</a></strong> code, public relation and advertising</li>
</ul>


<div class="rozdzial">'._('Support').'</div>
	<ul>
	<li><strong><a href="/mypage.php?userid=1789">Thathanka</a></strong> | <strong><a href="/mypage.php?userid=196">Quinto</a></strong> :: GK logo (the mole, different versions)</li>
	<li><strong>gosia</strong> MySQL support, sandwiches and more :)</li>
	<li><strong><a href="/mypage.php?userid=497">moose</a></strong> GK maps</li>
	</ul>



<div class="rozdzial">'._('Help').'</div>
<ul>
	<li><strong>sp2ong</strong> betatesting, idea, public relation and advertising:)</li>
	<li><strong>shchenka</strong> betatesting, language support</li>
	<li><strong>ZYR, Lion &amp; Aquaria</strong> betatesting</li>
	<li><strong>angelo</strong> programming support</li>
	<li><strong>Yergo</strong> coordinates parser</li>
	<li><strong><a href="/mypage.php?userid=30144">YvesProvence</a></strong> public relation and advertising</li>
</ul>

<div class="rozdzial">'._('Translations').'</div>
<!-- country alphabetical order
   - crowdin contributors (need rights) https://crowdin.com/project/geokrety/settings#reports-top-members
   -->
<ul>
  <li><strong>Albanian</strong> Hendri Saputra</li>
  <li><strong>Česky</strong> Pavel Kumpán, Ladislav Boháč, Matěj Volf, Ondra Kozel, Juraj Motuz, Pavel Sváda</li><!-- Czech - Tchèque -->
  <li><strong>Catalan</strong> SastRe.O</li>
  <li><strong>Dansk</strong> Niels Langkilde / oz9els</li>
  <li><strong>Deutsch</strong> SigmaZero, Grimpel, Schrottie, '.$lineflyer.', Rabenkind22</li>
  <li><strong>Eesti</strong> BeautyAndBeast</li>
  <li><strong>English</strong> '.$filips.', shchenka</li>
  <li><strong>Español</strong> Zugzwangy, todoporhallar, Xavi Rangel, Iori Yagami</li>
  <li><strong>Finnish</strong> abelard90</li>
  <li><strong>Français</strong> Arnaud Hubert, polaris45, Nam, Daimoneu, BSLLM, synergy14, Yves Pratter</li>
  <li><strong>Indonesian</strong> Saryulis, Hendri Saputra, saifulrahmad, Kartika Rizky, Syaukani, raviyanda</li>
  <li><strong>Italian</strong> Daimoneu, Olivier Renard</li>
  <li><strong>Latviešu</strong> mediamasterLV</li>
  <li><strong>Magyar</strong> M Ernő, Hoffmann Zsolt</li>
  <li><strong>Nederlands</strong> Team Engelenburg, '.$harrieklomp.'</li><!-- Dutch -->
  <li><strong>Polski</strong> '.$filips.', shchenka, brasiapl, Jakub Fabijan (Felidae), Piotr Juzwiak</li><!-- Polish -->
  <li><strong>Português</strong> Rui Alberto Almeida, Carlos</li>
  <li><strong>Русский</strong> Максим Милаков, Aleksandr Kostin, Сергей Штейнмиллер</li><!-- Russian -->
  <li><strong>Română</strong> Schiopu Claudiu</li>
  <li><strong>Suomi</strong> Ilpo Kantonen</li>
  <li><strong>Svenska</strong> fredrik, Jonas aka hjontemyra, Henrik Mattsson-Mårn </li><!-- Swedish -->
  <li><strong>Turkish</strong> samet pekel, galadriell, Semra</li>
  <li><strong>Global reviewer</strong>
    '.$lineflyer.', '.$kumy.', '.$filips.', Google translator ;)</li>
</ul>
';

/* manage geokrety credits */
$creditsStyle = '
    <style>
        .dcreds {
          padding: 10px;
        }

        .dcred {
          margin : 5px;
          padding : 5px;
          float:left;
          width:250px;
          height: 100px;
          border: 1px solid lightgrey;
        }

        .dcredimg {
          float:right;
        }

        .dcredname {
          padding: 10px;
        }
    </style>
';
$creditsConfig = $config['gk_credits'];
$credits = new \Geokrety\View\Credits($creditsConfig);
if ($credits->count() > 0) {
    $TRESC .= $creditsStyle;
    $TRESC .= '<div class="rozdzial">'._('Credits').'</div>';
    $TRESC .= $credits->toHtmlDivs();
}
/* manage geokrety credits - end */

require_once 'smarty.php';
