<div class="panel panel-default">
  <div class="panel-body">
    <ul class="links">
      <li>
        {fa icon="briefcase"}
        <a href="/mypage.php?userid={$user->id}&co=5">{t}View inventory{/t}</a>
      </li>
      <li>
        {fa icon="bolt"}
        <a href="/mypage.php?userid={$user->id}&co=1">{t}Owned GeoKrety{/t}</a>
      </li>
      <li>
        {fa icon="bolt"}
        <a href="/mypage.php?userid={$user->id}&co=2">{t}Watched GeoKrety{/t}</a>
      </li>
      <li>
        {fa icon="plane"}
        <a href="/mypage.php?userid={$user->id}&co=3">{t}Recently posted moves{/t}</a>
      </li>
      <li>
        {fa icon="plane"}
        <a href="/mypage.php?userid={$user->id}&co=4">{t}Moves of owned Geokrety{/t}</a>
      </li>
      <li>
        {fa icon="picture-o"}
        <a href="/galeria.php?photosby={$user->id}">{t}Posted pictures{/t}</a>
      </li>
      <li>
        {fa icon="picture-o"}
        <a href="/galeria.php?userid={$user->id}">{t}Owned GeoKrety pictures{/t}</a>
      </li>
      <li>
        {fa icon="map"}
        <a href="/gkmap.php{GEOKRETY_MAP_DEFAULT_PARAMS}{$user->username}">{t}Where are my GeoKrety?{/t}</a>
      </li>
      <li>
        {fa icon="bar-chart-o"}
        <a href="/user_stat.php?userid={$user->id}">{t}User stats{/t}</a>
      </li>
    </ul>
  </div>
</div>
