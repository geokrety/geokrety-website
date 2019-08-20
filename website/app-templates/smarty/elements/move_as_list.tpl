<tr class="{if $move->geokret->missing}danger{/if}">
    <td>
        {$move|logicon nofilter}
    </td>
    <td>
        {$move->geokret|gklink nofilter} {$move->geokret|gkavatar nofilter}<br />
        <small><span title="{$move->geokret->name}">{$move->geokret->name|truncate:30:"…"}</span></small>
    </td>
    <td>
        {$move->country|country nofilter}
        {$move|cachelink nofilter}
    </td>
    <td><span title="{$move->comment}">{$move->comment|truncate:60:"…"|markdown nofilter}</span></td>
    <td class="text-center" nowrap>
        {$move->moved_on_datetime|print_date nofilter}
        <br />
        <small>{$move->author|userlink:$move->username nofilter}</small>
    </td>
    <td class="text-right">{if $move->logtype->isCountingKilometers()}{$move->distance} km{/if}</td>
</tr>
