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
        <li><a href="/niusy.php">{fa icon="newspaper-o"} News</a></li>
        <li><a href="/ruchy.php"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Log a GeoKret</a></li>

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{fa icon="cogs"} Actions <span class="caret"></span></a>
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
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{fa icon="support"} Help <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/help.php">{fa icon="support"} Help</a></li>
            <li><a href="/molehole.php">{fa icon="bed"} Moleholes and GK hotels</a></li>
            <li><a href="/termsofuse.php">{fa icon="legal"} Term of use</a></li>
            <li><a href="/presscorner.php">{fa icon="newspaper-o"} Press corner</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/api.php">{fa icon="cog"} GK interface / API</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/statystyczka.php">{fa icon="bar-chart"} Statistics</a></li>
            <li><a href="/lost.php">{fa icon="grav"} Lost GeoKrety</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/download.php">{fa icon="download"} Downloads</a></li>
            <li><a href="https://geokretymap.org">{fa icon="map"} GeoKretyMap <span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a></li>
            <li><a href="/toolbox.php">{fa icon="cog"} GeoKrety Toolbox</a></li>
            <li><a href="/go2geo/">{fa icon="map-pin"} Waypoint resolver</a></li>
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
            <li><a href="/_admin.php">{fa icon="support"} Admin</a></li>
            {/if}
            <li role="separator" class="divider"></li>
            <li><a href="/mypage.php?co=5">{fa icon="briefcase"} My inventory</a></li>
            <li><a href="/mypage.php?co=1">{fa icon="bolt"} My GeoKrety</a></li>
            <li><a href="/mypage.php?co=2">{fa icon="binoculars"} Watched GeoKrety</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/mypage.php?co=3">{fa icon="plane"} My recent logs</a></li>
            <li><a href="/mypage.php?co=4">{fa icon="plane"} Recent moves of my GeoKrety</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/galeria.php?f=myown">{fa icon="picture-o"} My photos</a></li>
            <li><a href="/galeria.php?f=mygeokrets">{fa icon="picture-o"} Photos of my GeoKrety</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/mapka_kretow.php">{fa icon="map-marker"} Where are my GeoKrety?</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/longin.php?logout=1">{fa icon="sign-out"} Sign out</a></li>
          </ul>
        </li>
        {else}
        <li><a href="/adduser.php">{fa icon="user-plus"} Create account</a></li>
        <li><a href="/longin.php">{fa icon="sign-in"} Sign in</a></li>
        {/if}
      </ul>

    </div>




  </div>
</nav>
