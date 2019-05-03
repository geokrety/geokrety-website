<div class="panel panel-default">
  <div class="panel-heading">{t}Some statistics{/t}</div>
  <div class="panel-body">
    <img src="{$imagesUrl}/icons/solar-system.svg" class="intro-stats-icon"/>
    <p>
      {t escape=no
        gk_count=$stats.stat_geokretow
        gk_hidden=$stats.stat_geokretow_zakopanych
        user_count=$stats.stat_userow
      }
      <strong>%1</strong> registered GeoKrety,
      <strong>%2</strong> GeoKrety hidden
      <strong>%3</strong> users.
      {/t}
    </p>
    <p>
      🚀 {t escape=no
        gk_distance=$stats.stat_droga
        gk_distance_earth_moon=$stats.stat_droga_ksiezyc
        gk_distance_equator=$stats.stat_droga_obwod
        gk_distance_earth_sun=$stats.stat_droga_slonce
      }
      <strong>%1</strong> km done by all GeoKrety
      (it is <strong>%2</strong> x distance from the Earth to the Moon 🌛,
      <strong>%3</strong> x the Earth equatorial circumference 🌍
      and <strong>%4</strong> x the distance from the Earth to the Sun 🌞).
      {/t}
    </p>
    <p>
      {t}And that's thanks to you! Congratulation!{/t} 👏
    </p>
  </div>
</div>
