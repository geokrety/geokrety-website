{include file='macros/paginate.tpl'}

<a class="anchor" id="watched"></a>
<h2>{t}Watched GeoKrety{/t}</h2>

{if $geokrety}
{call pagination total=$geokretyTotal perpage=$geokretyPerPage anchor='watched'}
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th></th>
        <th>{t}ID{/t}</th>
        <th class="text-center">{t}Owner{/t} {sort column='owner' type='alpha' anchor='owned'}</th>
        <th>{t}Spotted in{/t}</th>
        <th class="text-center">{t}Last move{/t}</th>
        <th class="text-right"><img src="{$imagesUrl}/log-icons/dist.gif" title="{t}Distance{/t}" /></th>
        <th class="text-right"><img src="{$imagesUrl}/log-icons/2caches.png" title="{t}Caches count{/t}" /></th>
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
          <small><span title="{$geokret->name}">{$geokret->name|truncate:30:"…"}</span></small>
        </td>
        <td class="text-center">
          {userlink user=$geokret->owner()}
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
{call pagination total=$geokretyTotal perpage=$geokretyPerPage anchor='watched'}
{else}
  {if $user->isCurrentUser()}
    <em>{t}You have any watched GeoKrety.{/t}</em>
  {else}
    <em>{t escape=no username=$user->username}%1 has no GeoKrety in his watch list.{/t}</em>
  {/if}
{/if}
