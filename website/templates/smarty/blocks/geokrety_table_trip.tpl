{include file='macros/paginate.tpl'}

{if isset($tripTotal)}
{call pagination total=$tripTotal perpage=$tripPerPage anchor='trip'}
{/if}
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th></th>
        <th>{t}ID{/t}{if isset($tripTotal)} {sort column='id' type='numeric' anchor='trip'}{/if}</th>
        <th>{t}Spotted in{/t}{if isset($tripTotal)} {sort column='waypoint' type='alpha' anchor='trip'}{/if}</th>
        <th>{t}Comment{/t}</th>
        <th class="text-center">{t}Last move{/t}{if isset($tripTotal)} {sort column='ru.data' type='amount' anchor='trip'}{/if}</th>
        <th class="text-right"><img src="{$imagesUrl}/log-icons/dist.gif" title="{t}Distance{/t}" />{if isset($tripTotal)} {sort column='droga' type='numeric' anchor='trip'}{/if}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>

      {foreach from=$trip item=step}
      <tr class="{if $step->geokret->missing}danger{/if}">
        <td>
          {logicon gk=$step->geokret}
        </td>
        <td>
          {gklink gk=$step->geokret} {gkavatar gk=$step->geokret}<br />
          <small><span title="{$step->geokret->name}">{$step->geokret->name|truncate:30:"…"}</span></small>
        </td>
        <td>
          {country_flag country=$step->country}
          {cachelink tripStep=$step}
        </td>
        <td><span title="{$step->comment}">{$step->comment|truncate:60:"…"}</span></td>
        <td nowrap>
          {Carbon::parse($step->ruchData)->diffForHumans()}
          <br />
          <small>{userlink user=$step->author()}</small>
        </td>
        <td>{if in_array($step->logType, array('0', '3', '5'))}{$step->distance} km{/if}</td>
        <td>
          {if $step->geokret->isOwner()}
          <a class="btn btn-warning btn-xs" href="/edit.php?co=geokret&id={$step->geokret->id}" title="{t}Update this GeoKret{/t}">
            {fa icon="pencil"}
          </a>
          {/if}
          {if $step->geokret->hasCurrentUserSeenGeokretId()}
          <a href="/ruchy.php?nr={$step->geokret->trackingCode}" title="{t}Log this GeoKret{/t}">{fa icon="smile-o"}</a>
          {/if}
        </td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>
