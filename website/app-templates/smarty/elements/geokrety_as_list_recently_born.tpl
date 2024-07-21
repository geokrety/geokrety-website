<tr class="{if $geokret->isMissing()}danger{elseif $geokret->isArchived()}danger{elseif !$geokret->owner}info{/if}">
    <td>
        {$geokret|gkicon nofilter}{$geokret|gklink nofilter} {$geokret|gkavatar nofilter}<br />
        <small>{$geokret->gkid}</small>
    </td>
    <td class="text-center">
        {$geokret->owner|userlink nofilter}
    </td>
    <td class="text-center" nowrap>
        {$geokret->born_on_datetime|print_date nofilter}
        <br />
    </td>
    <td>
    </td>
</tr>
