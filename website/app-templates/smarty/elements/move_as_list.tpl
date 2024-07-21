{if $move.subset}
{foreach from=$move.subset item=move}
<tr class="{$move->id} {if $move->geokret->isMissing()}danger{elseif $move->move_type->isArchive()}danger{/if}">
    <td>
        {$move|logicon nofilter}
    </td>
    <td>
        {$move->geokret|gkicon nofilter} {$move->geokret|gklink nofilter} {$move->geokret|gkavatar nofilter}<br />
        <small>{$move->geokret->gkid}</small>
    </td>
    <td>
        {if !is_null($move->lat) and !is_null($move->lon)}{$move->country|country nofilter}{/if}
        {$move|cachelink nofilter}
    </td>
    <td><span title="{$move->comment|markdown:'text'}">{$move->comment|markdown:'text'|truncate:80:"(…)" nofilter}</span></td>
    <td class="text-center" nowrap>
        {$move->moved_on_datetime|print_date nofilter}
        <br />
        <small>{$move->author|userlink:$move->username nofilter}</small>
    </td>
    <td class="text-right">{if $move->move_type && $move->move_type->isCountingKilometers()}{$move->distance|distance}{/if}</td>
</tr>
{/foreach}
{/if}
