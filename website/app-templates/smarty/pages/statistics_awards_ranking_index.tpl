{extends file='base.tpl'}

{block name=title}{t}All yearly rankings{/t}{/block}

{block name=content}
    <h1>{t}All yearly rankings{/t}</h1>

    <div class="alert alert-info" role="alert">
        <ul>
            <li>{t}Since 2024, legacy badge "top droppers" has been renamed to "top loggers" as it best match the underlying query.{/t}</li>
            <li>{t}Since 2024, new "top spreads" badge appeared. It only counts "drop" movements.{/t}</li>
        </ul>
    </div>

    {if $awards_groups !== false}
        <ul>
        {foreach from=$awards_groups item=item}
            <li>
                <a href="{'statistics_awards_ranking'|alias:sprintf('@award=%s', $item->name)}">
                    {$item->name}: {$item->description}
                </a>
            </li>
        {/foreach}
        </ul>
        {else}
        <em>{t}No attributed awards yet.{/t}</em>
    {/if}
{/block}

{block name=javascript}
{/block}
