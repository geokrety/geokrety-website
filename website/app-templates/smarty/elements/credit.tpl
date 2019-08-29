<div class="dcred">
    {if isset($credit.icon)}
    <div class="dcredimg"{if isset($credit.icon_style)} style="{$credit.icon_style}{/if}">
        <img src="{$credit.icon}" alt="{t name=$credit.name}%1 logo{/t}" title="{t name=$credit.name}%1 logo{/t}" width="{if $credit.icon_width}{$credit.icon_width}{else}100px{/if}">
    </div>
    {/if}
    <div class="dcredname">
        {if isset($credit.link)}<a href="{$credit.link}">{$credit.name}</a>{else}{$credit['name']}{/if}
        {if isset($credit.desc)} : {$credit.desc nofilter}{/if}
    </div>
</div>
