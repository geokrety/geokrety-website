{extends file='base.tpl'}

{block name=title}🙋 {t}Authentication history{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_DATATABLE_CSS) && ''}
{\Assets::instance()->addJs(GK_CDN_DATATABLE_JS) && ''}

{include file='macros/pagination.tpl'}
{block name=content}
<a class="anchor" id="results"></a>

<h2>🙋 {t}Authentication history{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        {if $authentications_count}
        <div class="table-responsive">
            <table id="userAuthenticationHistory" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>{t}Status{/t}</th>
                        <th>{t}Method{/t}</th>
                        <th>{t}IP Address{/t}</th>
                        <th>{t}User-Agent{/t}</th>
                        <th>{t}Date{/t}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        {else}

        <em>{t}No activity yet!{/t}</em>

        {/if}

    </div>
    <div class="col-xs-12 col-md-3">
{*        {include file='blocks/user/actions.tpl'}*}
    </div>
</div>

{/block}

{include file='macros/datatable.tpl'}
{block name=javascript}
$('#userAuthenticationHistory').dataTable({
    {call common alias='user_authentication_history'}
    "searching": false,
    "order": [[ 2, 'desc' ]],
    "columns": [
        { "name": "succeed" },
        { "name": "method" },
        { "name": "created_on_datetime" },
        { "name": "ip" },
        { "name": "user_agent" }
    ],
});
{/block}
