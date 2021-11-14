{extends file='base.tpl'}

{block name=title}{t award=$award_group->name}Top %1{/t}{/block}

{block name=content}
    <h1>{t award=$award_group->name}Top %1{/t}</h1>
    <p>{$award_group->description}</p>
    {if $awards !== false}
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>{t}Rank{/t}</th>
        {foreach from=$awards item=item}
            {if $item->rank > 1}
                {break}
            {/if}
                <th>{$item->year}</th>
        {/foreach}
            </tr>
        </thead>
        <tbody>
        {assign "rank" 0}
        {foreach from=$awards item=item}
            {if $rank !== $item->rank}
                {if $item->rank > 1}
                    </tr>
                {/if}
                {assign "rank" $item->rank}
                <tr>
                    <td>#{$item->rank}</td>
            {/if}

            <td class="test user-{if !is_null($item->user)}{$item->user->id}{/if}">
                {$item->user|userlink nofilter}
                <br>
                <small>{$item->count}</small>
            </td>
        {/foreach}
        </tr>
        </tbody>
    </table>
        {else}
        <em>{t}No attributed awards yet.{/t}</em>
    {/if}
{/block}

{block name=javascript}
{/block}
