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
            <ol>
                <li><a href="#apiratelimit_currentlimits">Current API rate limits</a></li>
            </ol>
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
            <li><code>0</code> = Dropped to (location mandatory)</li>
            <li><code>1</code> = Grabbed from (no location)</li>
            <li><code>2</code> = A comment (no location)</li>
            <li><code>3</code> = Seen in (location optional)</li>
            <li><code>4</code> = Archived (no location ; limited to owner)</li>
            <li><code>5</code> = Visiting (location mandatory)</li>
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
            So, if someone drop/meet, its state will be <code>1</code>,
            and grab/dip will be <code>0</code>.
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
                <pre>{'api_v1_export2'|alias}?tracking_code={$gk_example_4_tc}</pre>
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
            <dd>GeoKret's Tracking Code (ex: <code>{$gk_example_4_tc}</code> or multiple (limit {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) separated by comma <code>{$gk_example_4_tc},{$gk_example_3_tc}</code>)</dd>

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

<h3>Rate limits</h3>
<a class="anchor" id="apiratelimit"></a>

<div class="panel panel-default">
    <div class="panel-body">

        <div class="alert alert-info" role="alert" style="margin-bottom:16px">
            <b>Quick summary</b>
            <ul style="margin-bottom:0">
                <li><b>Two layers:</b> (1) a global <i>burst</i> limit per minute across endpoints; (2) per-endpoint <i>quota</i> over a longer period (leaky bucket).</li>
                <li><b>Who’s counted:</b> anonymous traffic is counted per <b>IP address</b>; authenticated traffic is counted per <b>account</b>.</li>
                <li><b>Exceeding a limit:</b> we return <code>HTTP 429</code> and standard rate-limit headers.</li>
            </ul>
        </div>

        <p>To keep the service reliable and abuse-resistant, we apply rate limiting.</p>
        <p>If you hit a limit, we respond with <code>429 Too Many Requests</code>.</p>

        <h4>How it works</h4>
        <ul>
            <li><b>Layer 1 – Burst (RPM):</b> caps short spikes across all endpoints per minute.</li>
            <li><b>Layer 2 – Endpoint quota:</b> each API endpoint also has a longer-period allowance enforced with the
                <a href="https://en.wikipedia.org/wiki/Leaky_bucket" target="_blank" rel="noopener">Leaky Bucket algorithm</a>.
            </li>
        </ul>

        <blockquote cite="https://en.wikipedia.org/wiki/Leaky_bucket">
            The leaky bucket analogy: water can be added intermittently, leaks at a constant rate, and overflows when full.
            <img src="{GK_CDN_IMAGES_URL}/help/Leaky_bucket_analogy.svg" class="img-responsive" width="170" height="240" alt="Leaky bucket analogy">
        </blockquote>

        <h5>Check usage</h5>
        <p>
            Your current usage is exposed in the API response headers and via a dedicated endpoint:
        </p>
        <ul>
            <li>Anonymous: <a href="{'api_v1_rate_limit_usage'|alias}">{'api_v1_rate_limit_usage'|alias}</a></li>
            <li>Authenticated: <a href="{'api_v1_rate_limit_usage'|alias}?secid=&lt;secid_here&gt;">{'api_v1_rate_limit_usage'|alias}?secid=&lt;secid_here&gt;</a></li>
        </ul>

        <pre><code class="language-xml">{$rate_limit_usage}</code></pre>

        <h5>Example: 429 response</h5>
        <pre><code>HTTP/1.1 429 Too Many Requests
Ratelimit-Limit: 750
Ratelimit-Remaining: 0
X-RateLimit-Limit: 750
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1725100800
X-Ratelimit-Resource: API_V1_EXPORT2
X-GK-Rate-Limit: API_V1_EXPORT2 0/750 (86400)
X-GK-Rate-Limit-Exceeded: true
Content-Type: application/xml</code></pre>
         <pre><code class="language-xml">{$gk_xml_rate_limit_error}</code></pre>

        <h4>Current API rate limits</h4>
        <a class="anchor" id="apiratelimit_currentlimits"></a>
        <p>
            Your effective limit is the base multiplied by your tier multiplier (the <b>period</b> does not change):
            <code>effective_limit = floor(base_limit × tier_multiplier)</code>
        </p>

        <table class="table table-striped table-sm">
            <caption class="text-muted" style="caption-side: bottom">
                Endpoint quotas per tier (leaky bucket).
            </caption>
            <thead>
            <tr>
                <th>endpoint</th>
                <th>period_s</th>
                <th>base_limit</th>
                {foreach from=$smarty.const.RATE_LIMIT_LEVEL_MULTIPLIER key=tier item=mult}
                    <th>{$tier} (×{$mult})</th>
                {/foreach}
            </tr>
            </thead>
            <tbody>
            {foreach GK_RATE_LIMITS_DEFAULT as $limit => $values}
                {assign var=base value=$values[0]}
                {assign var=period value=$values[1]}
                <tr>
                    <td>{$limit}</td>
                    <td>{$period}</td>
                    <td>{$base}</td>
                    {foreach from=$smarty.const.RATE_LIMIT_LEVEL_MULTIPLIER key=tier item=mult}
                        <td>{math equation="floor(x*y)" x=$base y=$mult}</td>
                    {/foreach}
                </tr>
            {/foreach}
            </tbody>
        </table>

        <p class="text-muted">
            <em>Note: some endpoints (e.g. username changes) are account-only; anonymous rows are kept for consistency.</em>
        </p>

        <h4>Tiers and multipliers</h4>
        <ul>
            <li><b>Anonymous</b> — requests without an account or secure identifier; counted by IP address (×{$rate_limit_multipliers[$smarty.const.RATE_LIMIT_LEVEL_ANONYMOUS]}).</li>
            <li><b>User</b> — any logged-in account (×{$rate_limit_multipliers[$smarty.const.RATE_LIMIT_LEVEL_USER]}).</li>
            <li><b>Bronze</b> — small donation or occasional support (×{$rate_limit_multipliers[$smarty.const.RATE_LIMIT_LEVEL_BRONZE]}).</li>
            <li><b>Silver</b> — regular donation or ongoing support (×{$rate_limit_multipliers[$smarty.const.RATE_LIMIT_LEVEL_SILVER]}).</li>
            <li><b>Gold</b> — significant financial support for the project (×{$rate_limit_multipliers[$smarty.const.RATE_LIMIT_LEVEL_GOLD]}).</li>
            <li><b>Platinum</b> — exceptional or sustaining sponsorship (×{$rate_limit_multipliers[$smarty.const.RATE_LIMIT_LEVEL_PLATINUM]}).</li>
            <li><b>Maintainer</b> — core team and operational tasks (×{$rate_limit_multipliers[$smarty.const.RATE_LIMIT_LEVEL_MAINTAINER]}).</li>
        </ul>

        <h4>Tips to avoid 429s</h4>
        <ul>
            <li>Authenticate when possible to avoid sharing anonymous (IP-based) limits behind NAT.</li>
            <li>Batch and paginate requests; add small client-side delays or jitter.</li>
            <li>Honor <code>X-RateLimit-*</code> headers when present.</li>
        </ul>

        <h4>How to earn higher limits</h4>
        <ul>
            <li><b>Code &amp; QA:</b> bug fixes, features, security reports.</li>
            <li><b>Community:</b> translations, documentation, user support, issue triage.</li>
            <li><b>Sponsorship:</b> optional donations help cover hosting and maintenance.</li>
        </ul>
        <p>Once confirmed, your tier updates automatically (a short cache delay may apply). Non-financial contributions are valued equally.</p>

        <h4>Transparency</h4>
        <ul>
            <li>The table lists <b>base limits</b> per endpoint and <b>multipliers</b> per tier.</li>
            <li>Endpoints may expose your current allowance and tier so you can monitor usage.</li>
        </ul>

        <h4>Abuse &amp; fairness</h4>
        <ul>
            <li>Automated abuse, credential sharing, or bypass attempts may lead to temporary or permanent restrictions.</li>
            <li>We aim to notify and work with users to resolve accidental overuse.</li>
        </ul>

        <h4>Privacy</h4>
        <ul>
            <li>We store minimal counters in Redis keyed to anonymized identifiers.</li>
            <li>Anonymous usage is keyed by IP; authenticated usage is keyed to your account.</li>
            <li>Short-lived caches keep the service responsive.</li>
        </ul>

        <h4>Change management</h4>
        <ul>
            <li>Base limits or multipliers may be adjusted to keep the service healthy; material changes are announced in the changelog/release notes.</li>
            <li>If you believe your tier is incorrect, please contact us for a review.</li>
        </ul>

    </div>
</div>
{/block}
