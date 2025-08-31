{block name=modal_content}
<div class="modal-header alert-success">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to reset this rate-limit?{/t}</h4>
</div>

<form name="ResetRateLimitForm" action="{'admin_rate_limit_reset'|alias}" method="post">
    <div class="modal-body">
        {t escape=no name=$name key=$key}Name=%1 KEY=%2{/t}
        <input type="hidden" name="name" value="{$name|escape}">
        <input type="hidden" name="key" value="{$key|escape}">
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-success">{t}Reset{/t}</button>
    </div>
</form>
{/block}
