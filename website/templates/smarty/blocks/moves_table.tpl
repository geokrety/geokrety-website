{include file='macros/links_user.tpl'}
{include file='macros/links_gk.tpl'}
{include file='macros/links_cache.tpl'}
<div class="table-responsive">
  <table class="table table-striped">
    {foreach from=$recent_moves item=move}
    <tr>
      <td>{log_icon id=$move.id type=$move.logtype gk_type=$move.typ}</td>
      <td>
        {call gkLink id=$move.id}{if $move.plik}{pictureIcon filename=$move.plik}{/if}<br />
        <small><span title="{$move.nazwa}">{$move.nazwa|truncate:30:"…"}</span></small>
      </td>
      <td>
        {country_flag country=$move.country}
        {call cacheLink waypoint=$move.waypoint lat=$move.lat lon=$move.lon}
      </td>
      <td><span title="{$move.koment}">{$move.koment|truncate:60:"…"}</span></td>
      <td nowrap>
        <span title="{$move.data}">{Carbon::parse($move.data)->diffForHumans()}</span><br />
        {call userLink id=$move.owner username=$move.user username2=$move.username}
      </td>
      <td>{if in_array($move.logtype, array('0', '3', '5'))}{$move.droga} km{/if}</td>
    </tr>
    {/foreach}
  </table>
</div>
