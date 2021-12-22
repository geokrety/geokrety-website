{block name=modal_content}
<div class="modal-header alert-warning">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Refresh your secid?{/t}</h4>
</div>

<form name="user-refresh-secid" action="{'user_refresh_secid'|alias}" method="post">
    <div class="modal-body">

        <em>{t}Refreshing your secid will disconnect all applications which were connected to your account. You'll have to reauthenticate them.{/t}</em>

    </div>
    <div class="modal-footer">
        {call csrf}
        <a class="btn btn-default" href="{'user_details'|alias:sprintf('userid=%d', $currentUser->id)}" title="{t}Back to user page{/t}" data-dismiss="modal">
            {t}Dismiss{/t}
        </a>
        <button type="submit" class="btn btn-warning">{t}Refresh{/t}</button>
    </div>
</form>
{/block}
