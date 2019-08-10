{*include file='macros/paginate.tpl'*}
{extends file='base.tpl'}

{block name=content}
<a class="anchor" id="news"></a>

{*if isset($totalNews)}
{call pagination total=$totalNews perpage=$newsPerPage anchor='news'}
{/if*}

{foreach from=$news item=item}
{include file='elements/news.tpl'}
{/foreach}

{*if isset($totalNews)}
{call pagination total=$totalNews perpage=$newsPerPage anchor='news'}
{/if*}

{/block}

{block name=javascript}
{if $f3->get('SESSION.IS_LOGGED_IN')}
    // Bind modal
    {include 'js/dialog_news_subscription.js.tpl'}
{/if}
{/block}
