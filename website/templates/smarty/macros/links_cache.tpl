{function cacheLink}{* waypoint, lat, lon *}
{if $waypoint}
{$waypoint|waypoint_info assign="wpt"}
<a href="{$wpt.5}">{$waypoint}</a>
{if $wpt.2}<br /><small>{$wpt.2}</small>{/if}
{elseif $lat and $lon}
{$lat}/{$lon}<br />
<small>(<a href="http://www.geocaching.com/seek/nearest.aspx?origin_lat={$lat}&origin_long={$lon}&dist=1">{t}Search on geocaching.com{/t}</a>)</small>
{/if}
{/function}
