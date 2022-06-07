{if $geokrety.subset}
{foreach from=$geokrety.subset item=geokret}
<tr class="{if $geokret->missing}danger{/if}">
    <td>
        {$geokret|posicon nofilter}
    </td>
    <td>
        {$geokret|gklink nofilter} {$geokret|gkavatar nofilter}<br />
        <small>{$geokret->gkid}</small>
    </td>
    <td class="text-center">
        {$geokret->owner|userlink:'' nofilter}
    </td>
    <td class="text-center">
        {if !is_null($geokret->last_position) && $geokret->last_position->move_type->isCoordinatesRequired()}
        {$geokret->last_position->country|country nofilter}
        {$geokret->last_position|cachelink nofilter}
        {/if}
    </td>
    <td class="text-center" nowrap>
        {if !is_null($geokret->last_position)}
        {$geokret->last_position|logicon:true nofilter}
        {$geokret->last_position->moved_on_datetime|print_date nofilter}
        <br />
        <small>{$geokret->last_position->author|userlink:$geokret->last_position->username nofilter}</small>
        {else}
        {$geokret->created_on_datetime|print_date nofilter}
        {/if}
    </td>
    <td class="text-right">
        {$geokret->distance|distance}
    </td>
    <td class="text-right">
        {$geokret->caches_count}
    </td>
    <td class="text-right">
        {if $geokret->isHolder()}
        <a class="btn btn-default btn-xs move-link" href="{'move_create'|alias}?tracking_code={$geokret->tracking_code}" title="{t}Move this GeoKret{/t}">🛩️</a>
        {/if}
        {if $f3->get('SESSION.CURRENT_USER') == $f3->get('PARAMS.userid')}
        <a class="btn btn-default btn-xs unwatch-link" href="{'geokret_unwatch'|alias:sprintf('@gkid=%s', $geokret->gkid)}" title="{t}Remove this geokret from your watch list{/t}" data-toggle="modal" data-target="#modal" data-type="geokret-unwatch" data-id="{$geokret->gkid}">❌</a>
        {/if}
    </td>
</tr>
{/foreach}
{/if}
