<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title pull-left">
            <h3 class="panel-title">
                {if $f3->get('SESSION.CURRENT_USER')}
                {if $news->isSubscribed()}
                <button type="button" class="btn btn-default btn-xs" title="{t}Unsubscribe from news{/t}" data-toggle="modal" data-target="#modal" data-type="news-subscription" data-id="{$news->id}">
                    {fa icon="bell"}
                </button>
                {else}
                <button type="button" class="btn btn-default btn-xs" title="{t}Subscribe to news{/t}" data-toggle="modal" data-target="#modal" data-type="news-subscription" data-id="{$news->id}">
                    {fa icon="bell-slash"}
                </button>
                {/if}
                {/if}
                {$news->title}
            </h3>
        </div>
        <div class="panel-title pull-right">
            {$news->created_on_datetime|print_date nofilter}
            {$news|newslink nofilter}
            <i>({if !is_null($news->author)}{$news->author|userlink nofilter}{else}{$news->author_name}{/if})</i>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">{$news->content nofilter}</div>
</div>
