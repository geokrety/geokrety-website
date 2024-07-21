{function alertLimitDays}
<div class="alert alert-info" role="alert">
    <b>Note:</b> <code>export*</code> has a limit of retrieved data set at <code>{GK_API_EXPORT_LIMIT_DAYS} days</code>
    (ie you can download data changed in the past {GK_API_EXPORT_LIMIT_DAYS} days only). This should be
    enough to sync local OC nodes or other databases. To get older data use
    <a href="{GK_CDN_SERVER_URL}/exports/">static exports</a>.
</div>
{/function}
{extends file='base.tpl'}

{block name=title}{t}Help API{/t}{/block}

{\GeoKrety\Assets::instance()->addCss(GK_CDN_LIBRARIES_PRISM_CSS_URL) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIBRARIES_PRISM_JS_URL) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIBRARIES_PRISM_PHP_JS_URL) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIBRARIES_MARKUP_TEMPLATING_JS_URL) && ''}

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
                    <li><a href="#collectible_parked">Collectible and Parked</a></li>
                </ol>
            </li>
            <li>
                <a href="#synchronizing">Database synchronizing</a>
                <ol>
                    <li><a href="#syncmethod1">Method 1: <code>export</code> All GeoKrety & logs information</a></li>
                    <li><a href="#syncmethod2">Method 2: <code>export_oc</code> Only GeoKrety information</a></li>
                </ol>
            </li>
            <li>
                <a href="#retrieveinfo">Retrieving information</a>
                <ol>
                    <li><a href="#export2"><code>export2</code> Retrieve GeoKrety using some filters</a></li>
                    <ol>
                        <li><a href="#sample_output_without_headers">Sample output (without headers)</a></li>
                        <li><a href="#sample_output_with_details">Sample output (with details)</a></li>
                        <li><a href="#general_purpose_switches">General purpose switches</a></li>
                        <li><a href="#retriving_user_s_inventory">Retriving user's inventory</a></li>
                        <li><a href="#compressing_output">Compressing output</a></li>
                    </ol>
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
            <li><a href="#apiratelimit">Rate Limits</a></li>
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
        <p>Please refer to the <a href="{'help'|alias}#Chooselogtype">help page</a> for more details about each logtype.</p>
    </div>
</div>

<a class="anchor" id="geokretytypes"></a>
<div class="panel panel-default">
    <div class="panel-heading">GeoKrety Types</div>
    <div class="panel-body">
        <p>GeoKrety Types are internaly stored using a <code>small integer</code>. The current mapping is as follow:</p>
        <ul>
            <li><code>0</code> = Traditional</li>
            <li><code>1</code> = A book</li>
            <li><code>2</code> = A human</li>
            <li><code>3</code> = A coin</li>
            <li><code>4</code> = KretyPost</li>
            <li><code>5</code> = Pebble</li>
            <li><code>6</code> = Car</li>
            <li><code>7</code> = Playing card</li>
            <li><code>8</code> = Dog tag/pet</li>
            <li><code>9</code> = Jigsaw part</li>
        </ul>
        <p>Please refer to the <a href="{'help'|alias}#GeoKretytypes">help page</a> for more details about each type.</p>
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

<a class="anchor" id="collectible_parked"></a>
<div class="panel panel-default">
    <div class="panel-heading">Collectible and Parked</div>
    <div class="panel-body">
        <h4>Collectible</h4>
        <p>
            <i>Collectible</i> GeoKrety is the default status. When a Geokret is set to <i>non-collectible</i>, then only
            a restricted number of move types are allowed. For every users, they can log:
            <code>meet</code> and <code>comments</code>. While current <i>holder</i> will be allowed to: <code>dip</code>
            and <code>comment</code>.
        </p>

        <h4>Parked</h4>
        <p>
            <i>Parked</i> GeoKrety automatically imply <i>Collectible</i> status. When a Geokret is set to <i>parked</i>,
            in addition to <i>Collectible</i> restrictions, such GeoKrety will not appear in user inventory.
        </p>
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
    <div class="panel-heading">Method 1: <code>export</code> All GeoKrety & logs information (slow and large volume of data)</div>
    <div class="panel-body">
        <p>
            To get list of created GeoKrety after the date and moves registered
            after the date.
        </p>
        <sample>
            Example: <code>{'api_v1_export'|alias}?modifiedsince={$modified_since}</code>
        </sample>
        <h4>Sample output (without headers)</h4>
        <pre><code class="language-xml">{$gk_xml_export}</code></pre>
    </div>
</div>


<a class="anchor" id="syncmethod2"></a>
<div class="panel panel-default">
    <div class="panel-heading">Method 2: <code>export_oc</code> Only GeoKrety information (fast, only most important data; designed for OC sites)</div>
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
            Example: <code>{'api_v1_export_oc'|alias}?modifiedsince={$modified_since}</code>
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
    <div class="panel-heading"><code>export2</code> Retrieve GeoKrety using some filters</div>
    <div class="panel-body">

        <a class="anchor" id="sample_output_without_headers"></a>
        <h4>Sample output (without headers)</h4>
        <!-- HTML generated using hilite.me -->
        <pre><code class="language-xml">{$gk_xml_export2}</code></pre>
        <p>
            <ul>
                <li><code>last_pos_id</code> - id of the last position (waypoint)</li>
                <li><code>last_log_id</code> - id of the last log (may be a waypoint, comment etc)</li>
            </ul>
        </p>

        <a class="anchor" id="sample_output_with_details"></a>
        <h4>Sample output (with details)</h4>
        <code>{'api_v1_export2'|alias}?gkid={$gk_example_1}&details=1</code>
        <!-- HTML generated using hilite.me -->
        <pre><code class="language-xml">{$gk_xml_export2_details}</code></pre>

        <a class="anchor" id="general_purpose_switches"></a>
        <h3>General purpose switches</h3>
        <ul>
            <li>
                <code>modifiedsince</code> - list of GeoKrety with the timestamp
                of the last move <code>> modifiedsince</code> (required for some queries)
                <pre>{'api_v1_export2'|alias}?modifiedsince={$modified_since}</pre>
            </li>
            <li>
                <code>lonSW</code> <code>latSW</code> <code>lonNE</code> <code>latNE</code> -
                list of GeoKrety within the area (restricted to {GK_API_EXPORT_SURFACE_LIMIT}km²)
                <pre>{'api_v1_export2'|alias}?lonSW=0&latSW=45&lonNE=6&latNE=50</pre>
            </li>
            <li>
                <code>userid</code> - list GeoKrety owned by userid
                <pre>{'api_v1_export2'|alias}?userid=1</pre>
            </li>
            <li>
                <code>parked</code> - Include parked GeoKrety (works only with <code>userid</code>)
                <pre>{'api_v1_export2'|alias}?userid=1&parked=1</pre>
            </li>
            <li><code>gkid</code> - list only one GeoKret
                <pre>{'api_v1_export2'|alias}?gkid=141</pre>
            </li>
            <li><code>tracking_code</code> - list only one GeoKret, find by Tracking Code
                <pre>{'api_v1_export2'|alias}?tracking_code={$gk_example_3_tc}</pre>
            </li>
            <li><code>wpt</code> - list GeoKrety which are in the cache with
                the specified waypoint<br />
                <pre>{'api_v1_export2'|alias}?wpt=OP05E5</pre>
                It can be used to show GeoKrety in caches with defined waypoint's prefix; eg
                to show all GeoKrety in romanian caches (prefix GR), just enter:<br />
                <pre>{'api_v1_export2'|alias}?wpt=GR</pre>
            </li>
            <li><code>details</code> - show GeoKrety details, including moves and pictures. (compatible with GeoKretyMap)
                <pre>{'api_v1_export2'|alias}?gkid=141&details=1</pre>
            </li>
        </ul>

        <p>
            Above switches can be mixed. Eg to list my (<code>ownerid=1</code>) GeoKrety
            which are in <code>GC</code> caches, just enter:<br />
            <pre>{'api_v1_export2'|alias}?userid=1&wpt=GC</pre>
        </p>

        <a class="anchor" id="retriving_user_s_inventory"></a>
        <h3>Retriving user's inventory</h3>
        <ul>
            <li>
                <code>userid</code> and <code>inventory=1</code> - list GeoKrety
                in user's inventory
                <pre>{'api_v1_export2'|alias}?userid=1&inventory=1</pre>
            </li>
            <li>
                The same but with <code>secid</code> secret user's identification<br />

                <pre>{'api_v1_export2'|alias}?secid=xxx&inventory=1</pre>
                this request returns also the secret Tracking Codes
                (the <b>nr</b> variable) for all GeoKrety in user's inventory.
            </li>
        </ul>

        <a class="anchor" id="compressing_output"></a>
        <h3>Compressing output</h3>
        <ul>
            <li>adding <code>gzip=1</code> swich makes output compressed with
                gzip
                <pre>{'api_v1_export2'|alias}?userid=1&inventory=1&gzip=1</pre>
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
            <code>api-login2secid</code> via POST method. If correct login
            credentials are supplied, the <b>secid</b> is returned, else an
            HTTP 400 code is returned.
        </p>
        <sample>
            Example: <code>$ curl -X POST {'api_v1_login2secid'|alias} --data "login=someone&password=userpassword"</code>
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
            <b>Important</b>: for legacy reasons default timezone is <code>Europe/Paris</code>.
        </div>

        <h4>Base informations</h4>
        <dl class="dl-horizontal">
            <dt>variable</dt>
            <dd>description</dd>

            <dt><code>secid</code></dt>
            <dd>authentication string - see <a href="#secid">above</a></dd>

            <dt><code>tracking_code</code></dt>
            <dd>GeoKret's Tracking Code (ex: <code>GH68MA</code>)</dd>

            <dt><code>formname</code></dt>
            <dd><b>must</b> have value of <code>ruchy</code></dd>

            <dt><code>logtype</code></dt>
            <dd>the move logtype, see help on the <a href="#logtypes">top of this document</a> (ex: <code>0</code>)</dd>

            <dt><code>date</code></dt>
            <dd>the log date YYYY-MM-DD (ex: <code>2012-12-15</code>)</dd>

            <dt><code>hour</code></dt>
            <dd>hour HH (ex: <code>15</code>)</dd>

            <dt><code>minute</code></dt>
            <dd>minutes MM (ex: <code>23</code>)</dd>

            <dt><code>tz</code></dt>
            <dd>timezone to use (default: <code>Europe/Paris</code>)</dd>

            <dt><code>comment</code></dt>
            <dd>(<i>optional</i>) comment to the log (ex: <code>It is a good place for this GeoKret!</code>)</dd>

            <dt><code>app</code></dt>
            <dd>(<i>optional</i>) application name, <=16 chars (ex: <code>c:geo</code>)</dd>

            <dt><code>app_ver</code></dt>
            <dd>(<i>optional</i>) application version, <=16 chars (ex: <code>2019.06.06</code>)</dd>
        </dl>

        <h4>Drop or dipped move types</h4>
        <p>
            In addition to the basic informations above, some move type require additional values.
        </p>
        <dl class="dl-horizontal">
            <dt>variable</dt>
            <dd>description</dd>

            <dt><code>coordinates</code></dt>
            <dd><a href="{'help'|alias}#acceptableformats">latitude and longitude</a> (ex: <code>52.1534 21.0539</code>)</dd>

            <dt><code>waypoint</code></dt>
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

<h3>Rate Limits</h3>
<a class="anchor" id="apiratelimit"></a>
<div class="panel panel-default">
    <div class="panel-body">
        <p>In order to protect our service from abuse/misbehaving client… We have rate limiting in place.</p>
        <p>If you hit a rate limit, we wil respond with the usual http code <code>429</code>.</p>
        <p>A first set of rule limit the request rate per minutes, for any pages.</p>
        <p>A second set of rule limit the API calls over a period of time. We're using the <a href="https://en.wikipedia.org/wiki/Leaky_bucket" target="_blank">Leaky Bucket Algorithm</a>.</p>
        <blockquote  cite="https://en.wikipedia.org/wiki/Leaky_bucket">
            The leaky bucket analogy. Water can be added intermittently to the bucket, which leaks out at a constant
            rate until empty, and will also overflow when full.
            <img src="https://upload.wikimedia.org/wikipedia/commons/7/77/Leaky_bucket_analogy.svg" class="img-responsive" width="170" height="240">
        </blockquote>
        <p>The limits are set per IP or per secid depending if the call is authenticated or not.</p>
        <p>
            Your current API usage is available in the headers of each API call. You can also get your current Rate Limit usage using this endpoint:
        </p>
        <ul>
            <li>Anonymous: <a href="{'api_v1_rate_limit_usage'|alias}">{'api_v1_rate_limit_usage'|alias}</a></li>
            <li>Authenticated: <a href="{'api_v1_rate_limit_usage'|alias}?secid=&lt;secid_here&gt;">{'api_v1_rate_limit_usage'|alias}?secid=&lt;secid_here&gt;</a></li>
        </ul>
        <pre><code class="language-xml">{$rate_limit_usage}</code></pre>
    </div>
    <p>Current API rate limits are:</p>
    <ul>
        {foreach GK_RATE_LIMITS as $limit => $values}
            <li>{$limit}
                <ul>
                    <li>max requests: {$values[0]}</li>
                    <li>period: {$values[1]}s</li>
                </ul>
            </li>
        {/foreach}
    </ul>
</div>
{/block}
