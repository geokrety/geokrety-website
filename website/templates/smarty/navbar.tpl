<nav class="navbar-inverse navbar-fixed-top">
  <div class="container-fluid">

    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/">GeoKrety.org</a>
    </div>

    <div class="collapse navbar-collapse" id="bs-navbar-collapse">
      <ul class="nav navbar-nav">
        <li class="active"><a href="/"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Home</a></li>
        <li><a href="/niusy.php"><i class="fa fa-newspaper-o"></i> News</a></li>
        <li><a href="/ruchy.php"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Log a GeoKret</a></li>

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-cogs"></i> Actions <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/ruchy.php"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Log a GeoKret</a></li>
            <li><a href="/register.php"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Create a new GeoKret</a></li>
            <li><a href="/claim.php"><span class="glyphicon glyphicon-heart" aria-hidden="true"></span> Claim a GeoKret</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/szukaj.php"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Advanced search</a></li>
            <li><a href="/galeria.php"><span class="glyphicon glyphicon-picture" aria-hidden="true"></span> Photo gallery</a></li>
          </ul>
        </li>
      </ul>

      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-support"></i> Help <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/help.php"><i class="fa fa-support"></i> Help</a></li>
            <li><a href="/molehole.php"><i class="fa fa-bed"></i> Moleholes and GK hotels</a></li>
            <li><a href="/termsofuse.php"><i class="fa fa-legal"></i> Term of use</a></li>
            <li><a href="/presscorner.php"><i class="fa fa-newspaper-o"></i> Press corner</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/api.php"><i class="fa fa-cog"></i> GK interface / API</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/statystyczka.php"><i class="fa fa-bar-chart"></i> Statistics</a></li>
            <li><a href="/lost.php"><i class="fa fa-grav"></i> Lost GeoKrety</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/download.php"><i class="fa fa-download"></i> Downloads</a></li>
            <li><a href="/geomapa.php"><i class="fa fa-map"></i> Garmin map of caches</a></li>
            <li><a href="https://geokretymap.org"><i class="fa fa-map"></i> GeoKretyMap <span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a></li>
            <li><a href="/toolbox.php"><i class="fa fa-cog"></i> GeoKrety Toolbox</a></li>
            <li><a href="/go2geo/"><i class="fa fa-map-pin"></i> Waypoint resolver</a></li>
          </ul>
        </li>
        {if isset($isLoggedIn) and $isLoggedIn}
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
            My account <span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="/mypage.php"><span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> Details</a></li>
            {if isset($isSuperUser) and $isSuperUser}
            <li><a href="/_admin.php"><i class="fa fa-lock"></i> Admin</a></li>
            {/if}
            <li role="separator" class="divider"></li>
            <li><a href="/mypage.php?co=5"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> My inventory</a></li>
            <li><a href="/mypage.php?co=1"><span class="glyphicon glyphicon-flash" aria-hidden="true"></span> My GeoKrety</a></li>
            <li><a href="/mypage.php?co=2"><span class="glyphicon glyphicon-sunglasses" aria-hidden="true"></span> Watched GeoKrety</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/mypage.php?co=3"><span class="glyphicon glyphicon-plane" aria-hidden="true"></span> My recent logs</a></li>
            <li><a href="/mypage.php?co=4"><span class="glyphicon glyphicon-plane" aria-hidden="true"></span> Recent moves of my GeoKrety</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/galeria.php?f=myown"><span class="glyphicon glyphicon-picture" aria-hidden="true"></span> My photos</a></li>
            <li><a href="/galeria.php?f=mygeokrets"><span class="glyphicon glyphicon-picture" aria-hidden="true"></span> Photos of my GeoKrety</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/mapka_kretow.php"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span> Where are my GeoKrety?</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/longin.php?logout=1"><i class="fa fa-sign-out"></i> Sign out</a></li>
          </ul>
        </li>
        {else}
        <li><a href="/longin.php"><i class="fa fa-sign-in"></i> Sign in</a></li>
        {/if}
      </ul>

    </div>




  </div>
</nav>
