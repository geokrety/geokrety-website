{if $geokrety.subset}
{foreach from=$geokrety.subset item=geokret}
<tr class="{if $geokret->isMissing()}danger{elseif $geokret->isArchived()}danger{/if}">
    <td>
        <span class="hidden">{$geokret->gkid}</span>
        {$geokret|posicon nofilter}
    </td>
    <td>
        {$geokret|gklink nofilter} {$geokret|gkavatar nofilter}<br />
        <small>{$geokret->gkid}</small>
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
        <a class="btn btn-default btn-xs" href="{'move_create'|alias}?tracking_code={$geokret->tracking_code}" title="{t}Move this GeoKret{/t}">🛩️</a>
        {/if}
    </td>
</tr>
{/foreach}
{/if}
