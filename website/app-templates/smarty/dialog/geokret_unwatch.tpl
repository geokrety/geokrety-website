{block name=modal_content}
<div class="modal-header alert-warning">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Remove this GeoKret from your watch list?{/t}</h4>
</div>

<form name="addToWatchListForm" action="{'geokret_unwatch'|alias}" method="post">
    <div class="modal-body">
        <p><em>{t}You will no longer receive updates of its journey in your daily mails.{/t}</em></p>
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-warning">{t}Unwatch{/t}</button>
    </div>
</form>
{/block}
