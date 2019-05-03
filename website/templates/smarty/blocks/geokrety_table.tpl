{include file='macros/links_user.tpl'}
{include file='macros/links_gk.tpl'}
<div class="table-responsive">
  <table class="table table-striped">
    {foreach from=$recent_geokrety item=geokret}
    <tr>
      <td>{call gk_icon gk_type=$geokret.type}</td>
      <td>
        {call gkLink id=$geokret.id}{if $geokret.avatar_filename}{pictureIcon filename=$geokret.avatar_filename}{/if}<br />
        <small><span title="{$geokret.name}">{$geokret.name|truncate:30:"…"}</span></small>
      </td>
      <td>
        {t escape=no user="{call userLink id=$geokret.userid username=$geokret.username}"}by %1{/t}
      </td>
      <td nowrap>
        <span title="{$geokret.date}">{Carbon::parse($geokret.date)->diffForHumans()}</span><br />
      </td>
      <td>
      </td>
    </tr>
    {/foreach}
  </table>
</div>
