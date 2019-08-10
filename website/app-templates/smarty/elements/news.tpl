<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title pull-left">
            <h3 class="panel-title">
                {if $item->isSubscribed()}
                <button type="button" class="btn btn-default btn-xs" title="{t}Unsubscribe from news{/t}" data-toggle="modal" data-target="#modal" data-type="news-subscription" data-id="{$item->id}">
                    {fa icon="bell"}
                </button>
                {else}
                <button type="button" class="btn btn-default btn-xs" title="{t}Subscribe to news{/t}" data-toggle="modal" data-target="#modal" data-type="news-subscription" data-id="{$item->id}">
                    {fa icon="bell-slash"}
                </button>
                {/if}
                {$item->title}
            </h3>
        </div>
        <div class="panel-title pull-right">
            {$item->updated_on_datetime|print_date nofilter}
            {$item|newslink nofilter}
            <i>({if $item->author}{$item->author|userlink nofilter}{else}{$item->author_name}{/if})</i>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">{$item->content nofilter}</div>
</div>
