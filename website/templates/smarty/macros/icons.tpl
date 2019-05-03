{function log_icon id=0 type=0 gk_type=0}
  <img src="{$imagesUrl}/log-icons/{$gk_type}/{$type}.png" alt="{t}log type icon{/t}" title="{t id=$id}Move: %1{/t} {logType2Text type=$type}" />
{/function}

{function gk_icon gk_type=0}
  <img src="{$imagesUrl}/log-icons/{$gk_type}/icon_25.jpg" alt="{t}GK type icon{/t}" title="{gkType2Text type=$gk_type}" />
{/function}

{function flag country='xyz'}
  <span class="flag-icon flag-icon-{$country}" title="{$country}"></span>
{/function}
