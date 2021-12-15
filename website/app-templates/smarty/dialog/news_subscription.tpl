{block name=modal_content}
<div class="modal-header {if $subscription->subscribed}alert-danger{else}alert-info{/if}">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">
        {if $subscription->subscribed}
        {t}Do you really want to unsubscribe from this news?{/t}
        {else}
        {t}Subscribe to this news?{/t}
        {/if}
    </h4>
</div>

<form name="comment" action="{'news_subscription'|alias:sprintf('newsid=%d', $subscription->news->id)}" method="post">
    <div class="modal-body">
        {if $subscription->subscribed}
        <input type="hidden" name="subscribe" value="off" />
        {else}
        <input type="hidden" name="subscribe" value="on" />
        {/if}

    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-info {if $subscription->subscribed}btn-danger{else}btn-info{/if}">
            {if $subscription->subscribed}
            {t}Unsubscribe{/t}
            {else}
            {t}Subscribe{/t}
            {/if}
        </button>
    </div>
</form>
{/block}
