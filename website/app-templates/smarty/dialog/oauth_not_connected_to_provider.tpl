{function text}
    <p>{t}You are not connected to this OAuth provider.{/t}</p>
{/function}

{block name=content}
<div class="panel panel-danger">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Detaching OAuth provider{/t}</h3>
    </div>
    <div class="panel-body">
        {call text}
    </div>
    {if isset($current_user) && !$current_user->hasAcceptedTheTermsOfUse()}
        <div class="panel-footer">
            <a href="{'user_details'|alias:sprintf('userid=%d', $current_user->id)}" class="btn btn-default" title="{t}Back to my profile{/t}">{t}Back to my profile{/t}</a>
        </div>
    {/if}
</div>
{/block}

{block name=modal_content_only}
<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Detaching OAuth provider{/t}</h4>
</div>

<div class="modal-body">
    {call text}
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
</div>
{/block}
