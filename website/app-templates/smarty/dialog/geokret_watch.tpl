{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Add this GeoKret to your watch list?{/t}</h4>
</div>

<form name="addToWatchListForm" action="{'geokret_watch'|alias}" method="post">
    <div class="modal-body">
        <p><em>{t}By adding the GeoKret to your watch list, you will receive updates of its journey in your daily mails.{/t}</em></p>
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-info">{t}Watch{/t}</button>
    </div>
</form>
{/block}
