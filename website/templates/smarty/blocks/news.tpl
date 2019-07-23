<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title pull-left">
            <h3 class="panel-title">
                {if isset($newsSubscription)}
                {if $newsSubscription->subscribed}

                <button type="button" class="btn btn-default btn-xs" title="{t}Unsubscribe from news{/t}" data-toggle="modal" data-target="#modal" data-type="news-subscription" data-id="{$item->id}">
                    {fa icon="bell"}
                </button>

                {else}

                <button type="button" class="btn btn-default btn-xs" title="{t}Subscribe to news{/t}" data-toggle="modal" data-target="#modal" data-type="news-subscription" data-id="{$item->id}">
                    {fa icon="bell-slash"}
                </button>
                {/if}
                {/if}
                {$item->title}
            </h3>
        </div>
        <div class="panel-title pull-right">
            {print_date date=$item->date}
            {newslink news=$item}
            <i>
                ({userlink user=$item->author()})
            </i>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">{$item->content nofilter}</div>
</div>
