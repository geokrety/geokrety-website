{extends file='base.tpl'}

{block name=title}{t}Admin actions on News{/t}{/block}

{block name=content}
    <h1>{t}Admin actions on News{/t}</h1>
    <a href="{'admin_news_create'|alias}" class="btn btn-success" title="{t}Create News{/t}">
        {fa icon="plus"}
    </a>
    {block name=news_list}{/block}
{/block}
{include file='blocks/news_list.tpl'}

{block news_actions}
<div class="btn-group" role="group" aria-label="...">
    <button type="button" class="btn btn-success btn-xs" title="{t}View{/t}" data-toggle="modal" data-target="#modal" data-type="admin-news-view" data-id="{$news->id}">
        {fa icon="eye"}
    </button>
    <a href="{'admin_news_edit'|alias:sprintf('newsid=%d', $news->id)}" class="btn btn-warning btn-xs" title="{t}Edit{/t}">
        {fa icon="pencil"}
    </a>
</div>
{/block}

{block name=javascript}
// Bind modal
{include 'js/dialogs/dialog_admin_news_view.tpl.js'}
{/block}

{block name=javascript_modal append}
{/block}
