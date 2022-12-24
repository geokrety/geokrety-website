{function text}
    {t provider=$oauth_provider->name}You are about to detach your GeoKrety account from your %1 account. You'll not be able to login via this provider anymore.{/t}
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
                <form method="POST" action="{'opauth_detach'|alias:sprintf('strategy', $oauth_provider->name)}">
                    {call csrf}
                    <button type="submit" id="detachProviderButton" class="btn btn-danger center-block">{t}Detach{/t}</button>
                </form>
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
        <form method="POST" action="{'opauth_detach'|alias:sprintf('strategy', $oauth_provider->name)}">
            {call csrf}
            <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
            <button type="submit" id="detachProviderButton" class="btn btn-danger">{t}Detach{/t}</button>
        </form>
    </div>
{/block}
