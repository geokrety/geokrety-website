<?php

require __DIR__.'/../../../vendor/autoload.php';

if (!is_null(getenv('GK_SENTRY_DSN'))) {
    \Sentry\init(['dsn' => getenv('GK_SENTRY_DSN'), 'environment' => getenv('GK_SENTRY_ENV'), 'release' => getenv('GIT_COMMIT')]);
}

$services['geocache'] = 'geocaching databases';
$services['games'] = 'other GPS games';
$services['trackable'] = 'trackable items';

$supported['geocache'][] = ['https://opencaching.pl/', 'Opencaching PL', 'OP', 'OP05E5'];
$supported['geocache'][] = ['https://www.opencaching.de/', 'Opencaching DE', 'OC', 'OC0531'];
$supported['geocache'][] = ['https://opencache.uk/', 'Opencaching UK', 'OK', 'OK0014'];
$supported['geocache'][] = ['http://www.opencaching.nl/', 'Opencaching NL', 'OB', 'OB1A8D'];
$supported['geocache'][] = ['http://www.opencaching.ro/', 'Opencaching RO', 'OR', 'OR00BD'];
$supported['geocache'][] = ['http://opencaching.cz/', 'Opencaching CZ', 'OZ', 'OZ0064'];
$supported['geocache'][] = ['http://www.opencaching.us/', 'Opencaching USA', 'OU', 'OU0004'];

$supported['geocache'][] = ['https://www.geocaching.com/', 'geocaching.com', 'GC', 'GC1X3Z0'];
$supported['geocache'][] = ['http://www.terracaching.com/', 'terracaching', 'TC', 'TCCWU'];
$supported['geocache'][] = ['http://navicache.com/', 'navicache', 'N', 'N00AB3'];
$supported['geocache'][] = ['http://www.gpsgames.org/index.php?option=com_wrapper&wrap=Geocaching', 'Geocaching @gpsgames.org', 'GE', 'GE0174'];
$supported['geocache'][] = ['http://geocaching.com.au/', 'Geocaching Australia', 'GA', 'GA0141'];
$supported['geocache'][] = ['http://www.geocaching.su/', 'GeoCaching Russia', 'GE/ VI/ MS/ TR/ EX/', 'TR/1470'];
$supported['geocache'][] = ['http://www.rejtekhely.ro/', 'Geocaching Transsylvania', 'RH', 'RH0004'];

//$supported['games'][] = Array('http://wpg.alleycat.pl/', 'WaypointGame', 'WPG', 'WPG1180');
$supported['games'][] = ['http://www.waymarking.com/', 'waymarking.com', 'WM', 'WM78XF'];
$supported['games'][] = ['http://www.gpsgames.org/index.php?option=com_wrapper&wrap=Shutterspot', 'ShutterSpot', 'SH', 'SH0030'];
$supported['games'][] = ['http://www.gpsgames.org/index.php?option=com_wrapper&wrap=Geodashing', 'Geodashing', 'GDnn-XXXX', 'GD96-YKIK'];
$supported['games'][] = ['http://trigpointinguk.com/', 'TrigpointingUK', 'TPXXXX', 'TP7379'];

$supported['trackable'][] = ['https://geokrety.org/', 'geokrety.org', 'GK', 'GK05E5'];
$supported['trackable'][] = ['http://www.geocaching.com/track/travelbugs.aspx', 'travelbugs', 'TB', 'TB2771P'];

// if get wpt value is set then: redirect
if (isset($_GET['wpt']) and strlen($_GET['wpt'])) {
    $wpt = substr((strip_tags($_GET['wpt'])), 0, 20);
    include_once 'go2geo.php';
    $link = go2geo($wpt);
    if (!is_null($link)) {
        header("Location: $link");
        exit;
    }
    $error = 'Unable to resolve this waypoint 😢';
}
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <title>go2geo :: resolve geocaching waypoints…</title>
  </head>
  <body>
    <div class="container">

      <div class="row justify-content-center">
        <div class="col-12 col-sm-6 col-md-4" style="height: 100vh;">

          <a href="<?php echo "${_SERVER['REQUEST_SCHEME']}://${_SERVER['HTTP_HOST']}"; ?>">
            <img src="https://cdn.<?php echo $_SERVER['HTTP_HOST']; ?>/images/the-mole-with-name.svg" class="mx-auto d-block">
          </a>
          <h5 class="text-center pb-3">Waypoint resolver</h5>
          <form>
            <div>
              <p>
                <div class="input-group">
                  <input type="text" class="form-control form-control-lg" id="wpt" name="wpt" placeholder="Waypoint" aria-label="Recipient's username" aria-describedby="submitButton">
                  <button type="submit" class="btn btn-outline-secondary" type="button" id="submitButton"><i class="bi bi-search"></i></button>
                </div>
                <span class="error"><?php echo $error; ?></span>
              </p>
            </div>
          </form>

          <p class="text-center text-secondary text-opacity-25 pt-5">
            <i class="bi bi-chevron-down" style="font-size:90px;"></i>
          </p>

        </div>
      </div>

      <hr>

      <div class="row justify-content-center">
        <div class="col-12 col-md-12">
          To be redirected to the apropriate page, just type:
          <b><?php echo "${_SERVER['REQUEST_SCHEME']}://${_SERVER['HTTP_HOST']}"; ?>/go2geo/</b> and <b>waypoint name</b>, eg:
          <pre><?php echo "${_SERVER['REQUEST_SCHEME']}://${_SERVER['HTTP_HOST']}"; ?>/go2geo/op05e5</pre>


<?php
foreach ($supported as $key => $gra) {
    echo <<< EOF
          <h4>$services[$key]</h4>

          <table class="table">
            <thead>
              <tr>
                <th scope="col">Service</th>
                <th scope="col">Code</th>
                <th scope="col">Sample</th>
              </tr>
            </thead>
            <tbody>
EOF;

    foreach ($gra as $linia) {
        echo <<< EOF
<tr>
  <td><a href="$linia[0]">$linia[1]</a></td>
  <td>$linia[2]</td>
  <td><a href="${_SERVER['REQUEST_SCHEME']}://${_SERVER['HTTP_HOST']}/go2geo/$linia[3]">try it with $linia[3]</a></td>
</tr>
EOF;
    }
    echo <<< EOF
            </tbody>
          </table>
EOF;
}
?>
        </div>
      </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
  </body>
</html>
