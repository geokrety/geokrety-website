{function alertLimitDays}
<div class="alert alert-info" role="alert">
    <b>Note:</b> <code>export*.php</code> has a limit of retrieved data set at <code>{GK_API_EXPORT_LIMIT_DAYS} days</code>
    (ie you can download data changed in the past {GK_API_EXPORT_LIMIT_DAYS} days only). This should be
    enough to sync local OC nodes or other databases. To get older data use
    <a href="{GK_CDN_SERVER_URL}/exports/">static exports</a>.
</div>
{/function}
{extends file='base.tpl'}

{block name=title}{t}Help API{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_LIBRARIES_PRISM_CSS_URL)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_PRISM_JS_URL)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_PRISM_PHP_JS_URL)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_MARKUP_TEMPLATING_JS_URL)}

{block name=content}
<ol class="breadcrumb">
    <li><a href="">Home</a></li>
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
        <p>Please refer to the <a href="help.php#Chooselogtype">help page</a> for more details about each logtype.</p>
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
        <p>Please refer to the <a href="help.php#GeoKretytypes">help page</a> for more details about each type.</p>
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
        <pre><code class="language-php">&lt;?php
$gk = 'GK1234';
$id = hexdec(substr($gk, 2));
var_dump($id);

# result:
# int(4660)</code></pre>

        <h4>Convert integer to GKID</h4>
        <pre><code class="language-php">&lt;?php
$id = 4660;
$gk = sprintf("GK%04X", $id);
var_dump($gk);

# result:
# string(6) "GK1234"</code></pre>

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
            Example: <code>{GK_SITE_BASE_SERVER_URL}/export.php?modifiedsince={$modified_since}</code>
        </sample>
        <h4>Sample output (without headers)</h4>
        <pre><code class="language-xml">{$gk_xml_export}</code></pre>
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
            Example: <code>{GK_SITE_BASE_SERVER_URL}/export_oc.php?modifiedsince={$modified_since}</code>
        </sample>

        <h4>Sample output (without headers)</h4>
        <!-- HTML generated using hilite.me -->
        <pre><code class="language-xml">{$gk_xml_export_oc}</code></pre>
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
        <pre><code class="language-xml">{$gk_xml_export2}</code></pre>
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
                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?modifiedsince={$modified_since}</pre>
            </li>
            <li>
                <code>lonSW</code> <code>latSW</code> <code>lonNE</code> <code>latNE</code> -
                list of GeoKrety within the area (restricted to {GK_API_EXPORT_SURFACE_LIMIT}km²)
                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?lonSW=0&latSW=45&lonNE=6&latNE=50</pre>
            </li>
            <li>
                <code>userid</code> - lists GeoKrety owned by userid
                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?userid=1</pre>
            </li>
            <li><code>gkid</code> - lists only one GeoKret
                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?gkid=141</pre>
            </li>
            <li><code>wpt</code> - lists GeoKrety which are in the cache with
                the specified waypoint<br />
                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?wpt=OP05E5</pre>
                It can be used to show GeoKrety in caches with defined waypoint's prefix; eg
                to show all GeoKrety in romanian caches (prefix GR), just enter:<br />
                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?wpt=GR</pre>
            </li>
        </ul>

        <p>
            Above swiches can be mixed. Eg to list my (<code>ownerid=1</code>) GeoKrety
            which are in <code>GC</code> caches, just enter:<br />
            <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?userid=1&wpt=GC</pre>
        </p>

        <h3>Retriving user's inventory</h3>
        <ul>
            <li>
                <code>userid</code> and <code>inventory=1</code> - list GeoKrety
                in user's inventory
                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?userid=1&inventory=1</pre>
            </li>
            <li>
                The same but with <code>secid</code> secret user's identification<br />

                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?secid=xxx&inventory=1</pre>
                this request returns also the secret Tracking Codes
                (the <b>nr</b> variable) for all GeoKrety in user's inventory.
            </li>
        </ul>

        <h3>Compressing output</h3>
        <ul>
            <li>adding <code>gzip=1</code> swich makes output compressed with
                gzip
                <pre>{GK_SITE_BASE_SERVER_URL}/export2.php?userid=1&inventory=1&gzip=1</pre>
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
                <pre>{GK_SITE_BASE_SERVER_URL}/rest/konkret/trip/read.php?id=67914&format=json</pre>
            </li>
            <li>
                <code>format=csv</code> - list latest 1000 trip steps for a given GeoKrety as CSV, eg:<br />
                <pre>{GK_SITE_BASE_SERVER_URL}/rest/konkret/trip/read.php?id=67914&format=csv</pre>
            </li>
            <li>
                <code>format=gpx</code> - list latest 1000 trip steps for a given GeoKrety as GPX, eg:<br />
                <pre>{GK_SITE_BASE_SERVER_URL}/rest/konkret/trip/read.php?id=67914&format=gpx</pre>
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
            Example: <code>$ curl -X POST {GK_SITE_BASE_SERVER_URL}/api-login2secid.php --data "login=someone&password=userpassword"</code>
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
            <code>{GK_SITE_BASE_SERVER_URL}/ruchy.php</code> script and pass the
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
            <dd>GeoKret's Tracking Code (ex: <code>GH68MA</code>)</dd>

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
            In addition to the basic informations above, some move type require additional values.
        </p>
        <dl class="dl-horizontal">
            <dt>variable</dt>
            <dd>description</dd>

            <dt><code>latlon</code></dt>
            <dd><a href="help.php#acceptableformats">latitude and longitude</a> (ex: <code>52.1534 21.0539</code>)</dd>

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
        <pre><code class="language-xml">{$gk_xml_ruchy_saved}</code></pre>

        <h4>On error</h4>
        <pre><code class="language-xml">{$gk_xml_ruchy_error}</code></pre>

    </div>
</div>

<a class="anchor" id="application"></a>
<div class="panel panel-default">
    <div class="panel-heading">Application name/version</div>
    <div class="panel-body">
        <p>
            If you provide an <code>app</code> name, it would be nice to send us
            an application icon as <b>svg</b> format (historically we required 16x16, png,
            eg <img src="{GK_CDN_IMAGES_URL}/api/icons/16/Opencaching.png" alt="app icon" />),
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
{/block}
