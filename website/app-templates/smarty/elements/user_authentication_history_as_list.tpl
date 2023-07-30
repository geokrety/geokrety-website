{if $authentications.subset}
{foreach from=$authentications.subset item=authentication}
<tr class="{if !$authentication->succeed}danger{else}success{/if}">
    <td>
        {if $authentication->succeed}{t}Success{/t}{else}{t}Failure{/t}{/if}
    </td>
    <td>
        {$authentication->method}
    </td>
    <td>
        {$authentication->created_on_datetime|print_date_iso_format nofilter}
    </td>
    <td>
        {$authentication->ip}
    </td>
    <td>
        {$authentication->user_agent}
    </td>
</tr>
{/foreach}
{/if}
