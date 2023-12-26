<tr class="{if $geokret->missing}danger{elseif $geokret->isArchived()}danger{elseif !$geokret->owner}info{/if}">
    <td>{$geokret|gkicon nofilter}</td>
    <td>
        {$geokret|gklink nofilter} {$geokret|gkavatar nofilter}<br />
        <small>{$geokret->gkid}</small>
    </td>
    <td class="text-center">
        {$geokret->owner|userlink nofilter}
    </td>
    <td class="text-center" nowrap>
        {$geokret->created_on_datetime|print_date nofilter}
        <br />
    </td>
    <td>
    </td>
</tr>
