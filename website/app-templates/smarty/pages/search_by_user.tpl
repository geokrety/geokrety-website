{extends file='base.tpl'}

{block name=title}👓 {t}Search users{/t}{/block}

{block name=content}
{include file='macros/pagination.tpl'}
<a class="anchor" id="search-by-user"></a>

<h2>👓️ {t search_user=$search_user}Found users matching: %1{/t}</h2>
<div class="row">
    <div class="col-xs-12">

        {if $users.subset}
        {call pagination pg=$pg anchor='search-by-user'}
        <div class="table-responsive">
            <table id="searchByUserTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>{t}Username{/t}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$users.subset item=item}
                    <tr>
                        <td>{$item|userlink nofilter}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        {call pagination pg=$pg anchor='search-by-user'}
        {else}

        <em>{t search_user=$search_user}No users matching: %1{/t}</em>
        {/if}

    </div>
</div>

{/block}
