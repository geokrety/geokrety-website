{function alertLimitDays}
<div class="alert alert-info" role="alert">
    <b>Note:</b> <code>export*.php</code> has a limit of retrieved data set at <code>10 days</code>
    (ie you can download data changed in the past 10 days only). This should be
    enough to sync local OC nodes or other databases. To get older data use
    <a href="{CONFIG_CDN_URL}/exports/">static exports</a>.
</div>
{/function}

<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li class="active">GeoKrety XML interface</li>
</ol>

<ol>
    <li>
        <a href="#read">Read</a>
        <ol>
            <li>
                <a href="#general">General</a>
                <ol>
                    <li><a href="#logtypes">Logtypes</a></li>
                    <li><a href="#geokretytypes">GeoKrety Types</a></li>
                    <li><a href="#idconversion">Reference number and id conversion</a></li>
                </ol>
            </li>
            <li>
                <a href="#synchronizing">Database synchronizing</a>
                <ol>
                    <li><a href="#syncmethod1">Method 1: <code>export.php</code> All GeoKrety & logs information</a></li>
                    <li><a href="#syncmethod2">Method 2: <code>export_oc.php</code> Only GeoKrety information</a></li>
                </ol>
            </li>
            <li>
                <a href="#retrieveinfo">Retrieving information</a>
                <ol>
                    <li><a href="#export2"><code>export2.php</code> Retrieve GeoKrety using some filters</a></li>
                    <li><a href="#tripexport">Retrieve GeoKrety moves</a></li>
                </ol>
            </li>
        </ol>
    </li>
    <li>
        <a href="#write">Write</a>
        <ol>
            <li><a href="#secid">secid</a></li>
            <li><a href="#logging">logging</a></li>
            <li><a href="#responses">responses</a></li>
            <li><a href="#application">Application name/version</a></li>
            <li><a href="#scriptsamples">Scripts samples</a></li>
        </ol>
    </li>
</ol>

<a class="anchor" id="read"></a>
<h1>Read</h1>

<a class="anchor" id="general"></a>
<h2>General</h2>

<a class="anchor" id="logtypes"></a>
<div class="panel panel-default">
    <div class="panel-heading">Logtypes</div>
    <div class="panel-body">
        <p>Logtypes are internaly stored using a <code>small integer</code>. The current mapping is as follow:</p>
        <ul>
            <li><code>0</code> = Dropped to</li>
            <li><code>1</code> = Grabbed from</li>
            <li><code>2</code> = A comment</li>
            <li><code>3</code> = Seen in</li>
            <li><code>4</code> = Archived</li>
            <li><code>5</code> = Visiting</li>
        </ul>
        <p>Please refer to the <a href="/help.php#Chooselogtype">help page</a> for more details about each logtype.</p>
    </div>
</div>

<a class="anchor" id="geokretytypes"></a>
<div class="panel panel-default">
    <div class="panel-heading">GeoKrety Types</div>
    <div class="panel-body">
        <p>GeoKrety Types are internaly stored using a <code>small integer</code>. The current mapping is as follow:</p>
        <ul>
            <li><code>0</code> = Traditional</li>
            <li><code>1</code> = A book/CD/DVD…</li>
            <li><code>2</code> = A human/pet</li>
            <li><code>3</code> = A coin</li>
            <li><code>4</code> = KretyPost</li>
        </ul>
        <p>Please refer to the <a href="/help.php#GeoKretytypes">help page</a> for more details about each type.</p>
    </div>
</div>

<a class="anchor" id="idconversion"></a>
<div class="panel panel-default">
    <div class="panel-heading">Reference number and id conversion</div>
    <div class="panel-body">
        <p>
            GeoKrety ids (aka public code) are internaly stored as <code>integer</code>, but
            this number is generally shown to users as <code>GK<em>XXXX</em></code> format.
            This reference number is simply a concatenation of the letters "GK"
            and the hexadecimal representation of the internal id padded with
            <code>0</code> up to 4 digits.
        </p>

        <h4>Convert GKID to integer</h4>
        <pre>
&lt;?php
$gk = 'GK1234';
$id = hexdec(substr($gk, 2));
var_dump($id);

# result:
# int(4660)
</pre>

        <h4>Convert integer to GKID</h4>
        <pre>
&lt;?php
$id = 4660;
$gk = sprintf("GK%04X", $id);
var_dump($gk);

# result:
# string(6) "GK1234"
</pre>

    </div>
</div>

<a class="anchor" id="synchronizing"></a>
<h2>Database synchronizing</h2>
<p>
    This can be used to synchronize your database with GK database - useful for
    OpenCaching and other geocaching projects as well as for other purposes 😉
</p>
<div class="alert alert-warning" role="alert">
    Please note, all opencaching sites should be using <a href="#syncmethod2">Method 2</a>!
</div>
{call alertLimitDays}

<a class="anchor" id="syncmethod1"></a>
<div class="panel panel-default">
    <div class="panel-heading">Method 1: <code>export.php</code> All GeoKrety & logs information (slow and large volume of data)</div>
    <div class="panel-body">
        <p>
            To get list of created GeoKrety after the date and moves registered
            after the date.
        </p>
        <sample>
            Example: <code>{CONFIG_SITE_BASE_URL}export.php?modifiedsince=20090901000000</code>
        </sample>
        <h4>Sample output (without headers)</h4>
        <pre>
<!-- HTML generated using hilite.me -->
<span style="color: #507090">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot; standalone=&quot;yes&quot; ?&gt;</span>
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
</pre>
    </div>
</div>


<a class="anchor" id="syncmethod2"></a>
<div class="panel panel-default">
    <div class="panel-heading">Method 2: <code>export_oc.php</code> Only GeoKrety information (fast, only most important data; designed for OC sites)</div>
    <div class="panel-body">
        <p>
            To get list of GK that changed location during last hour.
        </p>
        <div class="alert alert-info" role="alert">
            <b>Note:</b>
            <code>state</code> field may have different value than in other
            versions of export scripts. This is because OC sites only need to
            know if a GeoKret is in a cache or in the hands of some geocacher.
            So, if someone dips a GeoKret, its state will be <code>1</code>
            (grabbed).
        </div>
        <sample>
            Example: <code>{CONFIG_SITE_BASE_URL}export_oc.php?modifiedsince={php}echo date('YmdHis', time() - (1 * 60 * 60)){/php}</code>
        </sample>

        <h4>Sample output (without headers)</h4>
        <!-- HTML generated using hilite.me -->
        <pre>
<span style="color: #507090">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot; standalone=&quot;yes&quot; ?&gt;</span>
<span style="color: #007000">&lt;gkxml</span> <span style="color: #0000C0">version=</span><span style="background-color: #fff0f0">&quot;1.0&quot;</span> <span style="color: #0000C0">date=</span><span style="background-color: #fff0f0">&quot;2013-01-05 13:00:49&quot;</span><span style="color: #007000">&gt;</span>
    <span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;4849&quot;</span><span style="color: #007000">&gt;</span>
        <span style="color: #007000">&lt;name&gt;</span><span style="color: #507090">&lt;![CDATA[B&amp;B&#39;s Dragonfly]]&gt;</span><span style="color: #007000">&lt;/name&gt;</span>
        <span style="color: #007000">&lt;distancetravelled&gt;</span>1959<span style="color: #007000">&lt;/distancetravelled&gt;</span>
        <span style="color: #007000">&lt;state&gt;</span>0<span style="color: #007000">&lt;/state&gt;</span>
        <span style="color: #007000">&lt;position</span> <span style="color: #0000C0">latitude=</span><span style="background-color: #fff0f0">&quot;53.73917&quot;</span> <span style="color: #0000C0">longitude=</span><span style="background-color: #fff0f0">&quot;17.40568&quot;</span> <span style="color: #007000">/&gt;</span>
        <span style="color: #007000">&lt;waypoints&gt;</span>
            <span style="color: #007000">&lt;waypoint&gt;</span><span style="color: #507090">&lt;![CDATA[OP1003]]&gt;</span><span style="color: #007000">&lt;/waypoint&gt;</span>
        <span style="color: #007000">&lt;/waypoints&gt;</span>
    <span style="color: #007000">&lt;/geokret&gt;</span>
    <span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;3249&quot;</span><span style="color: #007000">&gt;</span>
        <span style="color: #007000">&lt;name&gt;</span><span style="color: #507090">&lt;![CDATA[GeoKretynka]]&gt;</span><span style="color: #007000">&lt;/name&gt;</span>
        <span style="color: #007000">&lt;distancetravelled&gt;</span>113<span style="color: #007000">&lt;/distancetravelled&gt;</span>
        <span style="color: #007000">&lt;state&gt;</span>1<span style="color: #007000">&lt;/state&gt;</span>
        <span style="color: #007000">&lt;position</span> <span style="color: #0000C0">latitude=</span><span style="background-color: #fff0f0">&quot;0.00000&quot;</span> <span style="color: #0000C0">longitude=</span><span style="background-color: #fff0f0">&quot;0.00000&quot;</span> <span style="color: #007000">/&gt;</span>
        <span style="color: #007000">&lt;waypoints&gt;</span>
            <span style="color: #007000">&lt;waypoint&gt;</span><span style="color: #507090">&lt;![CDATA[]]&gt;</span><span style="color: #007000">&lt;/waypoint&gt;</span>
        <span style="color: #007000">&lt;/waypoints&gt;</span>
    <span style="color: #007000">&lt;/geokret&gt;</span>
<span style="color: #007000">&lt;/gkxml&gt;</span>
</pre>
    </div>
</div>


<a class="anchor" id="retrieveinfo"></a>
<h2>Retrieving information</h2>
{call alertLimitDays}
<a class="anchor" id="export2"></a>
<div class="panel panel-default">
    <div class="panel-heading"><code>export2.php</code> Retrieve GeoKrety using some filters</div>
    <div class="panel-body">

        <h4>Sample output (without headers)</h4>
        <!-- HTML generated using hilite.me -->
        <pre>
<span style="color: #507090">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot; standalone=&quot;yes&quot; ?&gt;</span>
<span style="color: #007000">&lt;gkxml</span> <span style="color: #0000C0">version=</span><span style="background-color: #fff0f0">&quot;1.0&quot;</span> <span style="color: #0000C0">date=</span><span style="background-color: #fff0f0">&quot;2010-12-09 19:10:03&quot;</span><span style="color: #007000">&gt;</span>
    <span style="color: #007000">&lt;GeoKrety&gt;</span>
    	<span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;8143&quot;</span> <span style="color: #0000C0">dist=</span><span style="background-color: #fff0f0">&quot;251&quot;</span> <span style="color: #0000C0">lat=</span><span style="background-color: #fff0f0">&quot;53.14598&quot;</span> <span style="color: #0000C0">lon=</span><span style="background-color: #fff0f0">&quot;23.18567&quot;</span>
    	<span style="color: #0000C0">waypoint=</span><span style="background-color: #fff0f0">&quot;OP2FD9&quot;</span> <span style="color: #0000C0">owner_id=</span><span style="background-color: #fff0f0">&quot;3813&quot;</span> <span style="color: #0000C0">state=</span><span style="background-color: #fff0f0">&quot;0&quot;</span> <span style="color: #0000C0">type=</span><span style="background-color: #fff0f0">&quot;0&quot;</span>
    	<span style="color: #0000CC">last_pos_id=</span><span style="background-color: #fff0f0">&quot;11536&quot;</span> <span style="color: #0000CC">last_log_id=</span><span style="background-color: #fff0f0">&quot;11553&quot;</span>
    	<span style="color: #0000C0">image=</span><span style="background-color: #fff0f0">&quot;1273660644jr8sm.jpg&quot;</span><span style="color: #007000">&gt;</span>
    	<span style="color: #507090">&lt;![CDATA[Wiewireczka]]&gt;</span>
    	<span style="color: #007000">&lt;/geokret&gt;</span>
    <span style="color: #007000">&lt;/GeoKrety&gt;</span>
<span style="color: #007000">&lt;/gkxml&gt;</span>
</pre>
        <p>
            <ul>
                <li><code>last_pos_id</code> - id of the last position (waypoint)</li>
                <li><code>last_log_id</code> - id of the last log (may be a waypoint, comment etc)</li>
            </ul>
        </p>

        <h3>General purpose swiches</h3>
        <ul>
            <li>
                <code>modifiedsince</code> - list of GeoKrety with the timestamp
                of the last move <code>> modifiedsince</code> (required for some queries)
                <pre>{CONFIG_SITE_BASE_URL}export2.php?modifiedsince=20100901000000</pre>
            </li>
            <li>
                <code>latNE</code> <code>latSW</code> <code>lonNE</code> <code>lonSW</code> -
                list of GeoKrety within the area
                <pre>{CONFIG_SITE_BASE_URL}export2.php?latNE=50&latSW=40&lonNE=50&lonSW=0</pre>
            </li>
            <li>
                <code>userid</code> - lists GeoKrety owned by userid
                <pre>{CONFIG_SITE_BASE_URL}export2.php?userid=1</pre>
            </li>
            <li><code>gkid</code> - lists only one GeoKret
                <pre>{CONFIG_SITE_BASE_URL}export2.php?gkid=141</pre>
            </li>
            <li><code>wpt</code> - lists GeoKrety which are in the cache with
                the specified waypoint<br />
                <pre>{CONFIG_SITE_BASE_URL}export2.php?wpt=OP05E5</pre>
                It can be used to show GeoKrety in caches with defined waypoint's prefix; eg
                to show all GeoKrety in romanian caches (prefix GR), just enter:<br />
                <pre>{CONFIG_SITE_BASE_URL}export2.php?wpt=GR</pre>
            </li>
        </ul>

        <p>
            Above swiches can be mixed. Eg to list my (<code>ownerid=1</code>) GeoKrety
            which are in <code>GC</code> caches, just enter:<br />
            <pre>{CONFIG_SITE_BASE_URL}export2.php?userid=1&wpt=GC</pre>
        </p>

        <h3>Retriving user's inventory</h3>
        <ul>
            <li>
                <code>userid</code> and <code>inventory=1</code> - list GeoKrety
                in user's inventory
                <pre>{CONFIG_SITE_BASE_URL}export2.php?userid=1&inventory=1</pre>
            </li>
            <li>
                The same but with <code>secid</code> secret user's identification:<br />
                <pre>{CONFIG_SITE_BASE_URL}export2.php?secid=xxx&inventory=1</pre>
                this request returns also the secret tracking codes
                (the <b>nr</b> variable) for all GeoKrety in user's inventory.
            </li>
        </ul>

        <h3>Compressing output</h3>
        <ul>
            <li>adding <code>gzip=1</code> swich makes output compressed with
                gzip
                <pre>{CONFIG_SITE_BASE_URL}export2.php?userid=1&inventory=1&gzip=1</pre>
            </li>
        </ul>

    </div>
</div>


<a class="anchor" id="tripexport"></a>
<div class="panel panel-default">
    <div class="panel-heading">Retrieve GeoKrety moves</div>
    <div class="panel-body">
        <ul>
            <li>
                <code>format=json</code> - list latest 1000 trip steps for a given GeoKrety as JSON, eg:<br />
                <pre>{CONFIG_SITE_BASE_URL}rest/konkret/trip/read.php?id=67914&format=json</pre>
            </li>
            <li>
                <code>format=csv</code> - list latest 1000 trip steps for a given GeoKrety as CSV, eg:<br />
                <pre>{CONFIG_SITE_BASE_URL}rest/konkret/trip/read.php?id=67914&format=csv</pre>
            </li>
            <li>
                <code>format=gpx</code> - list latest 1000 trip steps for a given GeoKrety as GPX, eg:<br />
                <pre>{CONFIG_SITE_BASE_URL}rest/konkret/trip/read.php?id=67914&format=gpx</pre>
            </li>
        </ul>

    </div>
</div>

<a class="anchor" id="write"></a>
<h1>Write</h1>

<a class="anchor" id="secid"></a>
<div class="panel panel-default">
    <div class="panel-heading">secid</div>
    <div class="panel-body">
        <p>
            Logging of GeoKrety is possible for authenticated users. Authentication
            require the use of variable <code>secid</code> via <code>POST</code> method.
            The <code>secid</code> is 128 characters long string, unique for all
            users (<b>it should be kept secret like a password</b>). The
            <code>secid</code> can be obtained by passing
            variables <code>login</code> and <code>password</code> to the script
            <code>api-login2secid.php</code> via POST method. If correct login
            credentials are supplied, the <b>secid</b> is returned, else an
            HTTP 400 code is returned.
        </p>
        <sample>
            Example: <code>$ curl -X POST {CONFIG_SITE_BASE_URL}api-login2secid.php --data "login=someone&password=userpassword"</code>
            <pre>26sOchw8re8RE8i7HPTXx50q8aXBUeGhD0QzwPHkmGmyz3fenI6Il1zEfyt9fdmbBBPbisk21xuyLoJQPGFLQDBp3L5IhFjxCFdmc30KyhYeH79GK6O4oDXnst84KYUp</pre>
        </sample>
    </div>
</div>

<a class="anchor" id="logging"></a>
<div class="panel panel-default">
    <div class="panel-heading">logging</div>
    <div class="panel-body">
        <p>
            To log a GeoKret, you have to make a <code>POST</code> on
            <code>{CONFIG_SITE_BASE_URL}ruchy.php</code> script and pass the
            <code>secid</code> as well as other data you normally pass via form.
            Anonymous users are not allowed to use the write API.
        </p>

        <div class="alert alert-danger" role="alert">
            <b>Important</b>: All date time need to be provided as UTC and not as user local time.
            It's up to the poster to made the necessary conversion. As of today
            we still don't have any way to pass timezone, but it'll change in
            the future.
        </div>

        <h4>Base informations</h4>
        <dl class="dl-horizontal">
            <dt>variable</dt>
            <dd>description</dd>

            <dt><code>secid</code></dt>
            <dd>authentication string - see <a href="#secid">above</a></dd>

            <dt><code>nr</code></dt>
            <dd>GeoKret's tracking code (ex: <code>GH68MA</code>)</dd>

            <dt><code>formname</code></dt>
            <dd><b>must</b> have value of <code>ruchy</code></dd>

            <dt><code>logtype</code></dt>
            <dd>the move logtype, see help on the <a href="#logtypes">top of this document</a> (ex: <code>0</code>)</dd>

            <dt><code>data</code></dt>
            <dd>the log date YYYY-MM-DD (ex: <code>2012-12-15</code>)</dd>

            <dt><code>godzina</code></dt>
            <dd>hour HH (ex: <code>15</code>)</dd>

            <dt><code>minuta</code></dt>
            <dd>minutes MM (ex: <code>23</code>)</dd>

            <dt><code>comment</code></dt>
            <dd>(<i>optional</i>) comment to the log (ex: <code>It is a good place for this GeoKret!</code>)</dd>

            <dt><code>app</code></dt>
            <dd>(<i>optional</i>) application name, <=16 chars (ex: <code>c:geo</code>)</dd>

            <dt><code>app_ver</code></dt>
            <dd>(<i>optional</i>) application version, <=16 chars (ex: <code>2019.06.06</code>)</dd>

            <dt><code>mobile_lang</code></dt>
            <dd><i>(optional)</i> error messages language (ex: <code>pl_PL.UTF-8</code>)</dd>
        </dl>

        <h4>Drop or dipped move types</h4>
        <p>
            In addition to the basic informations above, some move type require
            som more values.
        </p>
        <dl class="dl-horizontal">
            <dt>variable</dt>
            <dd>description</dd>

            <dt><code>latlon</code></dt>
            <dd><a href="/help.php#acceptableformats">latitude and longitude</a> (ex: <code>52.1534 21.0539</code>)</dd>

            <dt><code>wpt</code></dt>
            <dd>the waypoint (ex: <code>OP05E5</code>)</dd>
        </dl>

    </div>
</div>

<a class="anchor" id="responses"></a>
<div class="panel panel-default">
    <div class="panel-heading">Responses</div>
    <div class="panel-body">
        <p>
            The actual write API always return it's responses as XML documents.
        </p>

        <h4>On success</h4>
        <!-- HTML generated using hilite.me -->
        <pre>
<span style="color: #507090">&lt;?xml version=&quot;1.0&quot; ?&gt;</span>
<span style="color: #007000">&lt;gkxml</span> <span style="color: #0000C0">version=</span><span style="background-color: #fff0f0">&quot;1.0&quot;</span> <span style="color: #0000C0">date=</span><span style="background-color: #fff0f0">&quot;2013-01-03 21:29:11&quot;</span><span style="color: #007000">&gt;</span>
    <span style="color: #007000">&lt;geokrety&gt;</span>
        <span style="color: #007000">&lt;geokret</span> <span style="color: #0000C0">id=</span><span style="background-color: #fff0f0">&quot;27334&quot;</span><span style="color: #007000">/&gt;</span>
    <span style="color: #007000">&lt;/geokrety&gt;</span>
<span style="color: #007000">&lt;/gkxml&gt;</span>
</pre>
        <h4>On error</h4>
        <pre>
<span style="color: #507090">&lt;?xml version=&quot;1.0&quot; ?&gt;</span>
<span style="color: #007000">&lt;gkxml</span> <span style="color: #0000C0">version=</span><span style="background-color: #fff0f0">&quot;1.0&quot;</span> <span style="color: #0000C0">date=</span><span style="background-color: #fff0f0">&quot;2013-01-03 21:13:51&quot;</span><span style="color: #007000">&gt;</span>
    <span style="color: #007000">&lt;errors&gt;</span>
        <span style="color: #007000">&lt;error&gt;</span>Wrong secid<span style="color: #007000">&lt;/error&gt;</span>
        <span style="color: #007000">&lt;error&gt;</span>Wrond date or time<span style="color: #007000">&lt;/error&gt;</span>
    <span style="color: #007000">&lt;/errors&gt;</span>
<span style="color: #007000">&lt;/gkxml&gt;</span>
</pre>

    </div>
</div>

<a class="anchor" id="application"></a>
<div class="panel panel-default">
    <div class="panel-heading">Application name/version</div>
    <div class="panel-body">
        <p>
            If you provide an <code>app</code> name, it would be nice to send us
            an application icon as <b>svg</b> format (historically we required 16x16, png,
            eg <img src="{CONFIG_CDN_IMAGES}/api/icons/16/Opencaching.png" alt="app icon" />),
            so we could display it in log entries along with the app info.
        </p>
    </div>
</div>

<h3>Sample scripts</h3>
<a class="anchor" id="scriptsamples"></a>
<div class="panel panel-default">
    <div class="panel-heading">Scripts samples</div>
    <div class="panel-body">
        <p>Here are sample scripts using GK api</p>
        <ul>
            <li><a href="https://gist.github.com/filipsPL/d5a1b191a69ea6775ba2">pyGK (python)</a></li>
            <li>phpGK (php)</li>
        </ul>
    </div>
</div>
