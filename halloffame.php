<?php

require_once '__sentry.php';

// Main page of GeoKrety śćńółżźóółłż

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 3800; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Hall of fame');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

$TRESC = '
<p><i>Idę sobie i patrzę, a_leją zasłużonych... B. Smoleń</i></p>

<div class="rozdzial">Developers</div>
	<ul>
	<li><strong><a href="mypage.php?userid=1">filips</a></strong> idea, code and design</li>
	<li><strong><a href="mypage.php?userid=6262">simor</a></strong> visions into code ;)</li>
	<li><strong><a href="mypage.php?userid=26422">kumy</a></strong> hosting, code and <a href="https://github.com/geokrety">planning GK v2</a></li>
</ul>


<div class="rozdzial">Support</div>
	<ul>
	<li><strong><a href="mypage.php?userid=1789">Thathanka</a></strong> | <strong><a href="mypage.php?userid=196">Quinto</a></strong> :: GK logo (the mole, different versions)</li>
	<li><strong>gosia</strong> MySQL support, sandwiches and more :)</li>
	<li><strong><a href="mypage.php?userid=497">moose</a></strong> GK maps</li>
	</ul>



<div class="rozdzial">Help</div>
<ul>
	<li><strong>sp2ong</strong> betatesting, idea, public relation and advertising:)</li>
	<li><strong><a href="mypage.php?userid=35313">BSLLM</a></strong> public relation and advertising</li>
	<li><strong>shchenka</strong> betatesting, language support</li>
	<li><strong>ZYR, Lion &amp; Aquaria</strong> betatesting</li>
	<li><strong>angelo</strong> programming support</li>
	<li><strong>Yergo</strong> coordinates parser</li>
</ul>

<div class="rozdzial">Translations</div>
<ul>
  <li><strong>English, Polski</strong> filips, shchenka</li>
	<li><strong>Русский</strong> Максим Милаков, Aleksandr Kostin</li>
	<li><strong>Português</strong> Rui Alberto Almeida, Carlos</li>
	<li><strong>Deutsch</strong> SigmaZero, Grimpel, Schrottie</li>
	<li><strong>Français</strong> Arnaud Hubert, polaris45, Nam</li>
	<li><strong>Česky</strong> Pavel Kumpán, Ladislav Boháč</li>
	<li><strong>Română</strong> Schiopu Claudiu</li>
	<li><strong>Español</strong> Zugzwangy</li>
	<li><strong>Dansk</strong> Niels Langkilde / oz9els</li>
	<li><strong>Magyar</strong> M Ernő, Hoffmann Zsolt</li>
	<li><strong>Eesti</strong> BeautyAndBeast</li>
	<li><strong>Suomi</strong> Ilpo Kantonen</li>
	<li><strong>Latviešu</strong> mediamasterLV</li>
	<li><strong>Svenska</strong> fredrik, Jonas aka hjontemyra</li>
	<li><strong>Nederlands</strong> Team Engelenburg, Harrie Klomp</li>
	<p></p>
	<li><strong>Other</strong> filips + php + google translator ;)</li>
	</ul>

<div class="rozdzial">The project is running using and thanks to</div>
<table>
<tr>
<td><img src="templates/debian-logo.png" width="113" height="149" alt="debian" /></td>
<td><img src="templates/apache-logo120.gif" width="120" height="34" alt="apache" /></td>
<td><img src="templates/mysql-logo.gif" width="120" height="62" alt="MySQL" /></td>
<td><img src="templates/php-logo.png" width="128" height="128" alt="php" /></td>
<td><img src="templates/bluefish-logo.png" width="107" height="100" alt="BlueFish" /></td>
</tr>
</table>

';

require_once 'smarty.php';
