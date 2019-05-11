<div class="panel panel-default">
  <div class="panel-body">
    <ul class="links">
      <li>
        {fa icon="briefcase"}
        <a href="/mypage.php?userid={$user->id}&co=5">View inventory</a>
      </li>
      <li>
        {fa icon="bolt"}
        <a href="/mypage.php?userid={$user->id}&co=1">My GeoKrety</a>
      </li>
      <li>
        {fa icon="bolt"}
        <a href="/mypage.php?userid={$user->id}&co=2">Watched GeoKrety</a>
      </li>
      <li>
        {fa icon="plane"}
        <a href="/mypage.php?userid={$user->id}&co=3">My recent logs</a>
      </li>
      <li>
        {fa icon="plane"}
        <a href="/mypage.php?userid={$user->id}&co=4">Recent Geokrety moves</a>
      </li>
      <li>
        {fa icon="picture-o"}
        <a href="/galeria.php?photosby={$user->id}">My photos</a>
      </li>
      <li>
        {fa icon="picture-o"}
        <a href="/galeria.php?userid={$user->id}">Photos of my GeoKrety</a>
      </li>
      <li>
        {fa icon="map-marker"}
        <a href="/mapka_kretow.php?userid={$user->id}">Where are my GeoKrety?</a>
      </li>
      <li>
        {fa icon="bar-chart-o"}
        <a href="/user_stat.php?userid={$user->id}">User stats</a>
      </li>
    </ul>
  </div>
</div>
