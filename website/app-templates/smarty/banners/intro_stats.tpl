<div class="panel panel-default">
  <div class="panel-heading">{t}Some statistics{/t}</div>
  <div class="panel-body">
    <img src="{GK_CDN_IMAGES_URL}/icons/solar-system.svg" class="intro-stats-icon" width="100" height="100" />
    <p>
      {t escape=no
        gk_count=$stats.stat_geokretow|default:0
        gk_hidden=$stats.stat_geokretow_zakopanych|default:0
        user_count=$stats.stat_userow|default:0
      }<strong>%1</strong> registered GeoKrety, <strong>%2</strong> GeoKrety hidden by <strong>%3</strong> users.{/t}
    </p>
    <p>
      🚀 {t escape=no
        gk_distance=$stats.stat_droga|default:0|distance
        gk_distance_earth_moon=$stats.stat_droga_ksiezyc|default:0
        gk_distance_equator=$stats.stat_droga_obwod|default:0
        gk_distance_earth_sun=$stats.stat_droga_slonce|default:0
      }<strong>%1</strong> done by all GeoKrety (it is <strong>%2</strong> x distance from the Earth to the Moon 🌛, <strong>%3</strong> x the Earth equatorial circumference 🌍 and <strong>%4</strong> x the distance from the Earth to the Sun 🌞).{/t}
    </p>
    <p>
      {t}And that's thanks to you! Congratulation!{/t} 👏
    </p>
  </div>
</div>
