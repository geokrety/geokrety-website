<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = 'Future extrapolation';

$TRESC = 'Here we present some mathematical predictions of the future of geokrety.prg website. We are very curious about how accurate they are and how well we can predict future ;)

<h2>Predictions from 2013-02-26</h2>
I\'ve extrapolated number of geokrets registered in our service next years.

<h3>Day model</h3>

<p><img src="templates/ekstrapolacja-model_dzienny.png" width="723" height="395" alt="plot" /></p>

<p><strong>Predictions:</strong></p>

<p><img src="templates/ekstrapolacja-model_dzienny-bar.png" width="680" height="417" alt="plot" /></p>

<table style="width: 300px;">
<tr><th>day</th><th>GK</th></tr>
 <tr><td>2013-01-01</td><td>29740</td></tr>
 <tr><td>2014-01-01</td><td>41793</td></tr>
 <tr><td>2015-01-01</td><td>55903</td></tr>
 <tr><td>2016-01-01</td><td>72072</td></tr>
 <tr><td>2017-01-01</td><td>90351</td></tr>
 <tr><td>2018-01-01</td><td>110640</td></tr>
 <tr><td>2019-01-01</td><td>132987</td></tr>
 <tr><td>2020-01-01</td><td>157393</td></tr>
 <tr><td>2021-01-01</td><td>183931</td></tr>
 <tr><td>2022-01-01</td><td>212457</td></tr>
 <tr><td>2023-01-01</td><td>243041</td></tr>
 <tr><td>2024-01-01</td><td>275683</td></tr>
 <tr><td>2025-01-01</td><td>310480</td></tr>
 <tr><td>2026-01-01</td><td>347243</td></tr>
 <tr><td>2027-01-01</td><td>386064</td></tr>
 <tr><td>2028-01-01</td><td>426942</td></tr>
 <tr><td>2029-01-01</td><td>469999</td></tr>
 <tr><td>2030-01-01</td><td>514999</td></tr></table>

<p>This more precise model says we will have geokret <strong>GKFFFF</strong> <strong>2015-08-12</strong>. I\'m so curious about the real date of that event!</p>
';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
