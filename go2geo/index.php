<?php
// if get wpt value is set then: redirect
if (isset($_GET['wpt'])) {
    //  $wpt = substr(mysqli_real_escape_string($link, strip_tags($_GET['wpt'])), 0, 20);
    $wpt = substr((strip_tags($_GET['wpt'])), 0, 20);
    include_once 'go2geo.php';
    $link = go2geo($wpt);
    if ($link != null) {
        header("Location: $link");
        exit;
    } else {
        $error = 'unable to resolve the waypoint :(';
    }
}
?>


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">

<head>
    <meta http-equiv="Content-Type" content="text/xml; charset=UTF-8" />
    <title>go2geo :: resolve geocaching waypoints...</title>
    <link rel="shortcut icon" href="../favicon.ico" />
    <link rel="stylesheet" type="text/css" href="go2geo.css" />

</head>
<body>

<div id="outer">
  <div id="middle">
    <div id="inner">
    <h1>go2geo β (beta) :: resolve geocaching waypoints</h1>
    <form name="formularz" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
    Waypoint:
    <input type="text" name="wpt" size="10" maxlength="10" value="<?php echo $wpt; ?>" /> <input type="submit" value=" go! " />
    <p><span class="error"><?php echo $error; ?></span></p>
    <hr />
    <span class="szare"><a href="help.php">info & supported waypoints</a> | by: filips</span>
    </form>
    </div>
  </div>
</div>

</body>
</html>
