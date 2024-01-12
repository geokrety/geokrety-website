{block name=news_list}
{include file='macros/pagination.tpl'}
<a class="anchor" id="news-list"></a>

{if isset($news) and $news.subset}
    {call pagination pg=$pg anchor='news-list'}
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th class="text-right">Posted date</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$news.subset item=news}
            <tr class="">
                <td>{$news->id}</td>
                <td>{$news->title}</td>
                <td>{$news->author_name}</td>
                <td class="text-right">{$news->created_on_datetime|print_date nofilter}</td>
                <td class="text-right">
                    {block news_actions}{/block}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {call pagination pg=$pg anchor='news-list'}
{elseif isset($search) and !empty($search)}
    <em>{t}No news match the current request.{/t}</em>
{/if}
{/block}
