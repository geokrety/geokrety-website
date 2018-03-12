<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('GK XML interface');

$one_hour_before = date('YmdHis', time() - (1 * 60 * 60));

$TRESC = '

Jump to: <a href="#read">Read</a> | <a href="#write">Write</a>

<h1>Read</h1>

<h2>General</h2>

<h3>Logtypes (state)</h3>

0 = Dropped to;
1 = Grabbed from;
2 = A comment;
3 = Seen in;
4 = Archived;
5 = Visiting;

<h3>GK Types (type)</h3>

0 = Traditional;
1 = A book/CD/DVD...;
2 = A human;
3 = A coin;
4 = KretyPost;

<h3>Reference number and id conversion</h3>

<div style="background: #f0f0f0; overflow:auto;width:auto;color:black;background:white;border:solid gray;border-width:.1em .1em .1em .8em;padding:.2em .6em;"><pre style="margin: 0; line-height: 125%">
$id=hexdec(substr($gk, 2, 4));
$gk=sprintf(&quot;GK%04X&quot;,$id);
</pre></div>


<h2>Database synchronising</h2>

<p>This can be used to synchronize your database with GK database - useful for OpenCaching and other geocaching projects as well as for other purposes ;) <u>Please note, all opencaching sites should be using Method 2!</u></p>

<p><b>Note:</b> export*.php has a limit of retrieved data set at 10 days (ie you can download data changed in the past 10 days only). This should be enough to sync local OC nodes or other databases. To get older data check: <a href="https://cdn.geokrety.org/exports/">here</a>.</p>

<h3>Method 1: All GeoKrety & logs information (slow and large volume of data)</h3>

To get list of GK created after the date and moves registered after the date:<br />
'.$config['adres'].'export.php?modifiedsince=20090901000000

<p>Sample output (without headers):</p>
<!-- HTML generated using hilite.me --><div style="background: #ffffff; overflow:auto;width:auto;color:black;background:white;border:solid gray;border-width:.1em .1em .1em .8em;padding:.2em .6em;"><pre style="margin: 0; line-height: 125%"><span style="color: #507090">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot; standalone=&quot;yes&quot; ?&gt;</span>
<span style="color: #007000">&lt;gkxml</span> <span style="color: #0000C0">version=</span><span style="background-color: #fff0f0">&quot;1.0&quot;</span> <span style="color: #0000C0">date=</span><span style="background-color: #fff0f0">&quot;2013-01-05 13:00:49&quot;</span><span style="color: #007000">&gt;</span>

 <span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;6664&quot;</span><span style="color: #007000">&gt;</span>
   <span style="color: #007000">&lt;name&gt;</span><span style="color: #507090">&lt;![CDATA[Piramidy]]&gt;</span><span style="color: #007000">&lt;/name&gt;</span>
   <span style="color: #007000">&lt;description&gt;</span><span style="color: #507090">&lt;![CDATA[by Terry Pratchett]]&gt;</span><span style="color: #007000">&lt;/description&gt;</span>
   <span style="color: #007000">&lt;owner</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;3807&quot;</span><span style="color: #007000">&gt;</span><span style="color: #507090">&lt;![CDATA[meteor2017]]&gt;</span><span style="color: #007000">&lt;/owner&gt;</span>
   <span style="color: #007000">&lt;datecreated&gt;</span>2010-01-17 17:43:50<span style="color: #007000">&lt;/datecreated&gt;</span>
   <span style="color: #007000">&lt;distancetravelled&gt;</span>408<span style="color: #007000">&lt;/distancetravelled&gt;</span>
	 <span style="color: #007000">&lt;state&gt;</span>0<span style="color: #007000">&lt;/state&gt;</span>
	 <span style="color: #007000">&lt;missing&gt;</span>0<span style="color: #007000">&lt;/missing&gt;</span>
   <span style="color: #007000">&lt;position</span> <span style="color: #0000C0">latitude=</span><span style="background-color: #fff0f0">&quot;49.48972&quot;</span> <span style="color: #0000C0">longitude=</span><span style="background-color: #fff0f0">&quot;18.96848&quot;</span> <span style="color: #007000">/&gt;</span>
   <span style="color: #007000">&lt;waypoints&gt;</span>
      <span style="color: #007000">&lt;waypoint&gt;</span><span style="color: #507090">&lt;![CDATA[OP41A1]]&gt;</span><span style="color: #007000">&lt;/waypoint&gt;</span>
   <span style="color: #007000">&lt;/waypoints&gt;</span>
   <span style="color: #007000">&lt;type</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;1&quot;</span><span style="color: #007000">&gt;</span><span style="color: #507090">&lt;![CDATA[A book/CD/DVD...]]&gt;</span><span style="color: #007000">&lt;/type&gt;</span>
<span style="color: #007000">&lt;/geokret&gt;</span>

 <span style="color: #007000">&lt;moves</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;284778&quot;</span><span style="color: #007000">&gt;</span>
   <span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;28328&quot;</span><span style="color: #007000">&gt;</span><span style="color: #507090">&lt;![CDATA[Indián 2]]&gt;</span><span style="color: #007000">&lt;/geokret&gt;</span>
   <span style="color: #007000">&lt;position</span> <span style="color: #0000C0">latitude=</span><span style="background-color: #fff0f0">&quot;50.33383&quot;</span> <span style="color: #0000C0">longitude=</span><span style="background-color: #fff0f0">&quot;13.51802&quot;</span> <span style="color: #007000">/&gt;</span>
   <span style="color: #007000">&lt;waypoints&gt;</span>
      <span style="color: #007000">&lt;waypoint&gt;</span><span style="color: #507090">&lt;![CDATA[GC1W85C]]&gt;</span><span style="color: #007000">&lt;/waypoint&gt;</span>
   <span style="color: #007000">&lt;/waypoints&gt;</span>
   <span style="color: #007000">&lt;date</span> <span style="color: #0000C0">moved=</span><span style="background-color: #fff0f0">&quot;2013-01-05 12:00:00&quot;</span> <span style="color: #0000C0">logged=</span><span style="background-color: #fff0f0">&quot;2013-01-05 12:48:01&quot;</span> <span style="color: #007000">/&gt;</span>
   <span style="color: #007000">&lt;user</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;17715&quot;</span><span style="color: #007000">&gt;</span><span style="color: #507090">&lt;![CDATA[sacreecoeur]]&gt;</span><span style="color: #007000">&lt;/user&gt;</span>
   <span style="color: #007000">&lt;comment&gt;</span><span style="color: #507090">&lt;![CDATA[Moje první GeoKrety]]&gt;</span><span style="color: #007000">&lt;/comment&gt;</span>
   <span style="color: #007000">&lt;logtype</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;0&quot;</span><span style="color: #007000">&gt;</span><span style="color: #507090">&lt;![CDATA[Dropped to]]&gt;</span><span style="color: #007000">&lt;/logtype&gt;</span>
<span style="color: #007000">&lt;/moves&gt;</span>

<span style="color: #007000">&lt;/gkxml&gt;</span>
</pre></div>



<h3>Method 2: Only GeoKrety information (fast, only most important data; designed for OC sites)</h3>

To get list of GK that changed location during last hour: '.$config['adres'].'export_oc.php?modifiedsince='.$one_hour_before.'<br/>

<p>State field may have different value than in other versions of export scripts. This is because OC sites only need to know if a geokret is in a cache or in the hands of some geocacher. So if someone dips a geokret, its state will be 1 (grabbed).</p>

<p><b>Note:</b> export*.php has a limit of retrieved data set at 10 days (ie you can download data changed in the past 10 days only). This should be enough to sync local OC nodes or other databases. To get older data check: <a href="https://cdn.geokrety.org/exports/">here</a>.</p>


<p>Sample output:</p>
<!-- HTML generated using hilite.me --><div style="background: #ffffff; overflow:auto;width:auto;color:black;background:white;border:solid gray;border-width:.1em .1em .1em .8em;padding:.2em .6em;"><pre style="margin: 0; line-height: 125%"><span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;4849&quot;</span><span style="color: #007000">&gt;</span>
   <span style="color: #007000">&lt;name&gt;</span><span style="color: #507090">&lt;![CDATA[B&amp;B&#39;s Dragonfly]]&gt;</span><span style="color: #007000">&lt;/name&gt;</span>
   <span style="color: #007000">&lt;distancetravelled&gt;</span>1959<span style="color: #007000">&lt;/distancetravelled&gt;</span>
   <span style="color: #007000">&lt;state&gt;</span>0<span style="color: #007000">&lt;/state&gt;</span>
   <span style="color: #007000">&lt;position</span> <span style="color: #0000C0">latitude=</span><span style="background-color: #fff0f0">&quot;53.73917&quot;</span> <span style="color: #0000C0">longitude=</span><span style="background-color: #fff0f0">&quot;17.40568&quot;</span> <span style="color: #007000">/&gt;</span>
   <span style="color: #007000">&lt;waypoints&gt;</span>
      <span style="color: #007000">&lt;waypoint&gt;</span><span style="color: #507090">&lt;![CDATA[OP1003]]&gt;</span><span style="color: #007000">&lt;/waypoint&gt;</span>
   <span style="color: #007000">&lt;/waypoints&gt;</span>
<span style="color: #007000">&lt;/geokret&gt;</span>
<span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;3249&quot;</span><span style="color: #007000">&gt;</span>
   <span style="color: #007000">&lt;name&gt;</span><span style="color: #507090">&lt;![CDATA[Geokretynka]]&gt;</span><span style="color: #007000">&lt;/name&gt;</span>
   <span style="color: #007000">&lt;distancetravelled&gt;</span>113<span style="color: #007000">&lt;/distancetravelled&gt;</span>
   <span style="color: #007000">&lt;state&gt;</span>1<span style="color: #007000">&lt;/state&gt;</span>
   <span style="color: #007000">&lt;position</span> <span style="color: #0000C0">latitude=</span><span style="background-color: #fff0f0">&quot;0.00000&quot;</span> <span style="color: #0000C0">longitude=</span><span style="background-color: #fff0f0">&quot;0.00000&quot;</span> <span style="color: #007000">/&gt;</span>
   <span style="color: #007000">&lt;waypoints&gt;</span>
      <span style="color: #007000">&lt;waypoint&gt;</span><span style="color: #507090">&lt;![CDATA[]]&gt;</span><span style="color: #007000">&lt;/waypoint&gt;</span>
   <span style="color: #007000">&lt;/waypoints&gt;</span>
<span style="color: #007000">&lt;/geokret&gt;</span>
</pre></div>


<h2>Retriving information</h2>

<p><b>Note:</b> export*.php has a limit of retrieved data set at 10 days (ie you can download data changed in the past 10 days only). This should be enough to sync local OC nodes or other databases. To get older data check: <a href="https://cdn.geokrety.org/exports/">here</a>.</p>


<p><b><u>'.$config['adres'].'export2.php</b></u></p>

<p>sample output:</p>
<!-- HTML generated using hilite.me --><div style="background: #ffffff; overflow:auto;width:auto;color:black;background:white;border:solid gray;border-width:.1em .1em .1em .8em;padding:.2em .6em;"><pre style="margin: 0; line-height: 125%"><span style="color: #507090">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot; standalone=&quot;yes&quot; ?&gt;</span>
<span style="color: #007000">&lt;gkxml</span> <span style="color: #0000C0">version=</span><span style="background-color: #fff0f0">&quot;1.0&quot;</span> <span style="color: #0000C0">date=</span><span style="background-color: #fff0f0">&quot;2010-12-09 19:10:03&quot;</span><span style="color: #007000">&gt;</span>
<span style="color: #007000">&lt;geokrety&gt;</span>
	<span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;8143&quot;</span> <span style="color: #0000C0">dist=</span><span style="background-color: #fff0f0">&quot;251&quot;</span> <span style="color: #0000C0">lat=</span><span style="background-color: #fff0f0">&quot;53.14598&quot;</span> <span style="color: #0000C0">lon=</span><span style="background-color: #fff0f0">&quot;23.18567&quot;</span>
	<span style="color: #0000C0">waypoint=</span><span style="background-color: #fff0f0">&quot;OP2FD9&quot;</span> <span style="color: #0000C0">owner_id=</span><span style="background-color: #fff0f0">&quot;3813&quot;</span> <span style="color: #0000C0">state=</span><span style="background-color: #fff0f0">&quot;0&quot;</span> <span style="color: #0000C0">type=</span><span style="background-color: #fff0f0">&quot;0&quot;</span>
	<span style="color: #0000CC">last_pos_id=</span><span style="background-color: #fff0f0">&quot;11536&quot;</span> <span style="color: #0000CC">last_log_id=</span><span style="background-color: #fff0f0">&quot;11553&quot;</span>
	<span style="color: #0000C0">image=</span><span style="background-color: #fff0f0">&quot;1273660644jr8sm.jpg&quot;</span><span style="color: #007000">&gt;</span>
	<span style="color: #507090">&lt;![CDATA[Wiewireczka]]&gt;</span>
	<span style="color: #007000">&lt;/geokret&gt;</span>
<span style="color: #007000">&lt;/geokrety&gt;</span>
<span style="color: #007000">&lt;/gkxml&gt;</span>
</pre></div>
last_pos_id - id of the last position (waypoint); last_log_id - id of the last log (may be a waypoint, comment etc)

<h3>General purpose swiches:</h3>

<ul>
<li>modifiedsince*; list of GK with the timestamp of the last move >modifiedsince; (required for some queries) example:<br />
'.$config['adres'].'export2.php?modifiedsince=20100901000000</li>

<li>swiches defining the area of places, where GK are:<br />
latNE latSW lonNE lonSW; example:<br />
'.$config['adres'].'export2.php?latNE=50&latSW=40&lonNE=50&lonSW=0</li>

<li>userid; lists GK owned by userid; example:<br />
'.$config['adres'].'export2.php?userid=1</li>

<li>gkid: lists only one GK, example:<br />
'.$config['adres'].'export2.php?gkid=141</li>

<li>wpt: lists GK which are in the cache with specified waypoint, eg:<br />
'.$config['adres'].'export2.php?wpt=op05e5<br />
it can be used to show GK in caches with defined waypoint\'s prefix; eg
to show all GK in romanian caches (prefix GR), just enter:<br />
'.$config['adres'].'export2.php?wpt=gr</li>
</ul>
<p>Above swiches can be mixed. Eg to list my (ownerid=1) geokrets
which are in GC caches, just enter:<br />
'.$config['adres'].'export2.php?userid=1&wpt=gc
</p>

<h3>Retriving user\'s inventory:</h3>
<p><ul>
<li>userid and inventory=1: list GKs in user\'s inventory, eg:<br />
'.$config['adres'].'export2.php?userid=1&inventory=1</li>
<li>the same but with secid user\'s identification:<br />
'.$config['adres'].'export2.php?secid=....&inventory=1<br />
this request returns also the secret tracking codes (the <b>nr</b> variable) for all geokrets in user\'s inventory</li>
</ul></p>

<h3>Compressing output</h3>
<ul>
<li>adding <b>gzip=1</b> swich makes output is compressed with gzip (gzencode), eg:<br />
'.$config['adres'].'export2.php?userid=1&inventory=1&gzip=1
</li>
</ul>


<a id="write"></a><h1>Write</h1>

<p><b>this part of API is highly experimental!</b></p>

<p><b>(1) secid</b></p> <p>Logging of geokrety is possible by passing to appropriate script variable <b>secid</b> via POST method. The <b>secid</b> is 128 characters long string, unique for all users (it should be kept secret like a password). The <b>secid</b> can be obtained by passing variables <b>login</b> and <b>password</b> to the script:</p>

<p>'.$config['adres'].'api-login2secid.php</p>

<p>via POST method. If correct login credentials are supplied, the <b>secid</b> is returned.</p>

<p><b>(2) logging</b></p>
<p>To log a geokret, you have to pass to the <b><a href="'.$config['adres'].'ruchy.php">'.$config['adres'].'ruchy.php</a></b> script the <b>secid</b> as well as other data you normally pass via form:</p>

<table>
<tr><th>variable</th><th>description</th><th>example</th></tr>
<tr><td><b>secid</b></td><td>authentication string - see above</td><td></td></tr>
<tr><td><b>nr</b></td><td>geokret\'s tracking code</td><td>GH68MA</td></tr>
<tr><td><b>formname = \'ruchy\'</b></td> <td>(must be set this way)</td><td>ruchy</td></tr>
<tr><td><b>logtype</b></td><td>see <i>logtypes</i>on the top of this document</td><td>0</td></tr>
<tr><td><b>data</b></td><td>the log date YYYY-MM-DD</td><td>2012-12-15</td></tr>
<tr><td><b>godzina</b></td><td>hour HH</td><td>15</td></tr>
<tr><td><b>minuta</b></td><td>minutes MM</td><td>23</td></tr>
<tr><td><b>comment</b></td><td>(<i>optional</i>) comment to the log</td><td>It is a good place for this geokret!</td></tr>
<tr><td><b>app</b></td><td>(<i>optional</i>) application name, <=16 chars</td><td>Locus</td></tr>
<tr><td><b>app_ver</b></td><td>(<i>optional</i>) application version, <=16 chars</td><td>1.16dev</td></tr>
<tr><td><b>mobile_lang</b></td><td><i>(optional)</i> error messages language;<br />for list of avaliable languages <a href="/rzeczy/lang/">see here</a></td><td>pl_PL.UTF-8</td></tr>
<tr><td><b></b></td><td></td><td></td></tr>
</table>

<p>The new position of geokret should be set by one of the following variables:</p>

<table>
<tr><th>variable</th><th>description</th><th>example</th></tr>
<tr><td><b>latlon</b></td><td>latitude and longitude (<a href="/help.php#acceptableformats">various formats acceptable</a>)</td><td>52.1534 21.0539</td></tr>
<tr><td><b>wpt</b></td><td>waypoint</td><td>OP05E5</td></tr>
</table>

<p>All those via POST method. After successful logging the geokret, the xml is returned, like <a href="'.$config['adres'].'export2.php?gkid=29001">in this example</a>.</p>

<p>After <b>successful</b> data submission the "no errors" code is returned along with gk number:</p>

<!-- HTML generated using hilite.me --><div style="background: #ffffff; overflow:auto;width:auto;color:black;background:white;border:solid gray;border-width:.1em .1em .1em .8em;padding:.2em .6em;"><pre style="margin: 0; line-height: 125%"><span style="color: #507090">&lt;?xml version=&quot;1.0&quot; ?&gt;</span>
<span style="color: #007000">&lt;gkxml</span> <span style="color: #0000C0">version=</span><span style="background-color: #fff0f0">&quot;1.0&quot;</span> <span style="color: #0000C0">date=</span><span style="background-color: #fff0f0">&quot;2013-01-03 21:29:11&quot;</span><span style="color: #007000">&gt;</span>
<span style="color: #007000">&lt;errors&gt;</span>
 <span style="color: #007000">&lt;error&gt;&lt;/error&gt;</span>
<span style="color: #007000">&lt;/errors&gt;</span>
<span style="color: #007000">&lt;geokrety&gt;</span>
 <span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;27334&quot;</span><span style="color: #007000">/&gt;</span>
<span style="color: #007000">&lt;/geokrety&gt;</span>
<span style="color: #007000">&lt;/gkxml&gt;</span>
</pre></div>


<p>If <b>errors</b> occurs, the XML error list is returned in the defined language, eg:
<pre>
<div style="background: #ffffff; overflow:auto;width:auto;color:black;background:white;border:solid gray;border-width:.1em .1em .1em .8em;padding:.2em .6em;"><pre style="margin: 0; line-height: 125%"><span style="color: #507090">&lt;?xml version=&quot;1.0&quot; ?&gt;</span>
<span style="color: #007000">&lt;gkxml</span> <span style="color: #0000C0">version=</span><span style="background-color: #fff0f0">&quot;1.0&quot;</span> <span style="color: #0000C0">date=</span><span style="background-color: #fff0f0">&quot;2013-01-03 21:13:51&quot;</span><span style="color: #007000">&gt;</span>
<span style="color: #007000">&lt;errors&gt;</span>
<span style="color: #007000">&lt;error&gt;</span>Wrong secid<span style="color: #007000">&lt;/error&gt;</span>
<span style="color: #007000">&lt;error&gt;</span>Wrond date or time<span style="color: #007000">&lt;/error&gt;</span>
<span style="color: #007000">&lt;/errors&gt;</span>
<span style="color: #007000">&lt;/gkxml&gt;</span>
</pre></div>
</pre>
</p>

<p>If you provide an <b>app</b> name, it would be nice to send us an application icon (16x16, png, eg <img src="'.CONFIG_CDN_IMAGES.'/api/icons/16/Opencaching.png" alt="app icon" />), that we could add short info about application in the log entry.</p>
<p>If you have any idea, how to improve this API or any function-request, feel free to <a href="kontakt.php">contact us</a>!</p>

<h3>Sample scripts</h3>
<p>Here are sample scripts for using GK api</p>
<ul>
<li><a href="https://gist.github.com/filipsPL/d5a1b191a69ea6775ba2">pyGK (python)</a></li>
<li>phpGK (php)</li>
</ul>

';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
