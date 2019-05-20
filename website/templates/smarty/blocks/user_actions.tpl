<div class="panel panel-default">
  <div class="panel-body">
    <ul class="links">
      <li>
        {fa icon="briefcase"}
        <a href="/mypage.php?userid={$user->id}&co=5">View inventory</a>
      </li>
      <li>
        {fa icon="bolt"}
        <a href="/mypage.php?userid={$user->id}&co=1">Owned GeoKrety</a>
      </li>
      <li>
        {fa icon="bolt"}
        <a href="/mypage.php?userid={$user->id}&co=2">Watched GeoKrety</a>
      </li>
      <li>
        {fa icon="plane"}
        <a href="/mypage.php?userid={$user->id}&co=3">Recently posted moves</a>
      </li>
      <li>
        {fa icon="plane"}
        <a href="/mypage.php?userid={$user->id}&co=4">Moves of owned Geokrety</a>
      </li>
      <li>
        {fa icon="picture-o"}
        <a href="/galeria.php?photosby={$user->id}">Posted pictures</a>
      </li>
      <li>
        {fa icon="picture-o"}
        <a href="/galeria.php?userid={$user->id}">Owned GeoKrety pictures</a>
      </li>
      <li>
        {fa icon="map"}
        <a href="/gkmap.php{GEOKRETY_MAP_DEFAULT_PARAMS}{$user->username}"> Where are my GeoKrety?</a>
      </li>
      <li>
        {fa icon="bar-chart-o"}
        <a href="/user_stat.php?userid={$user->id}">User stats</a>
      </li>
    </ul>
  </div>
</div>
