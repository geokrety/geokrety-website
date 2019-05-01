{include file='macros/paginate.tpl'}

<ol class="breadcrumb">
  <li><a href="/">{t}Home{/t}</a></li>
  <li class="active">{t}News{/t}</li>
</ol>

<a class="anchor" id="news"></a>

{if isset($totalNews)}
{call pagination total=$totalNews perpage=$newsPerPage anchor='news'}
{/if}

{foreach from=$news item=item}
{include file='blocks/news.tpl'}
{/foreach}

{if isset($totalNews)}
{call pagination total=$totalNews perpage=$newsPerPage anchor='news'}
{/if}
