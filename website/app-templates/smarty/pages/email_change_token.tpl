{extends file='base.tpl'}

{block name=title}{t}Confirm changing your email address{/t}{/block}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="modal-title" id="modalLabel">{t}Do you confirm changing your email address?{/t}</h4>
    </div>
    <div class="panel-body">

        <form class="form-horizontal" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>

            <div class="form-group">
                <label class="col-sm-2 control-label">Old email</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{$token->user->email}</p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">New email</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{$token->email}</p>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {call csrf}
                    {if $f3->get('SESSION.CURRENT_USER')}
                    <a class="btn btn-default" id="emailChangeDismissButton" href="{'user_details'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}" title="{t}Back to your profile{/t}">
                        {t}Dismiss{/t}
                    </a>
                    {else}
                    <a class="btn btn-default" id="emailChangeDismissButton" href="{'home'|alias}" title="{t}Back to homepage{/t}">
                        {t}Dismiss{/t}
                    </a>
                    {/if}
                    <button type="submit" id="emailChangeAcceptButton" class="btn btn-primary" name="validate" value="true">{t}Yes, change my email address{/t}</button>
                    <button type="submit" id="emailChangeRefuseButton" class="btn btn-danger" name="validate" value="false">{t}No, abort this request{/t}</button>
                </div>
            </div>
        </form>

    </div>
</div>
{/block}
