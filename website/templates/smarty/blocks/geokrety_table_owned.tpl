{include file='macros/paginate.tpl'}

<a class="anchor" id="owned"></a>
<h2>{t}Owned GeoKrety{/t}</h2>

{if $geokrety}
{call pagination total=$geokretyTotal perpage=$geokretyPerPage anchor='owned'}
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th></th>
        <th>{t}ID{/t} {sort column='id' type='numeric' anchor='owned'}</th>
        <th>{t}Spotted in{/t} {sort column='waypoint' type='alpha' anchor='owned'}</th>
        <th class="text-center">{t}Last move{/t} {sort column='ru.data' type='amount' anchor='owned'}</th>
        <th class="text-right"><img src="{$imagesUrl}/log-icons/dist.gif" title="{t}Distance{/t}" /> {sort column='droga' type='numeric' anchor='owned'}</th>
        <th class="text-right"><img src="{$imagesUrl}/log-icons/2caches.png" title="{t}Caches count{/t}" /> {sort column='skrzynki' type='numeric' anchor='owned'}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$geokrety item=geokret}
      <tr class="{if $geokret->missing}danger{/if}">
        <td>
          {posicon gk=$geokret}
        </td>
        <td>
          {gklink gk=$geokret} {gkavatar gk=$geokret}<br />
          <small><span title="{$geokret->name}">{$geokret->name|truncate:30:"…"|markdown nofilter}</span></small>
        </td>
        <td>
          {if !is_null($geokret->lastLog)}
          {country_flag country=$geokret->lastPosition->country}
          {cachelink tripStep=$geokret->lastPosition}
          {/if}
        </td>
        <td class="text-center" nowrap>
          {logicon gk=$geokret}
          {if !is_null($geokret->lastLog)}
          {print_date date=$geokret->lastLog->ruchData}
          <br />
          <small>{userlink user=$geokret->lastLog->author()}</small>
          {else}
          {print_date date=$geokret->datePublished}
          {/if}
        </td>
        <td class="text-right">
          {$geokret->distance}
        </td>
        <td class="text-right">
          {$geokret->cachesCount}
        </td>
        <td>
          {if $geokret->isOwner()}
          <a class="btn btn-warning btn-xs" href="{$geokret->editUrl()}" title="{t}Update this GeoKret{/t}">
            {fa icon="pencil"}
          </a>
          {/if}
          {if $geokret->hasCurrentUserSeenGeokretId()}
          <a href="{$geokret->ruchyUrl()}" title="{t}Log this GeoKret{/t}">{fa icon="smile-o"}</a>
          {/if}
        </td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>
{call pagination total=$geokretyTotal perpage=$geokretyPerPage anchor='owned'}
{else}
  {if $user->isCurrentUser()}
    <em>{t}Hey! You don't own any GeoKrety. Do you know you may <a href="register.php">create</a> them for free?{/t}</em>
  {else}
    <em>{t escape=no username=$user->username}%1 has not yet created any GeoKrety.{/t}</em>
  {/if}
{/if}
