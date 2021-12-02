
{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL)}
{include file='macros/recaptcha.tpl'}

{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Contact user{/t}</h4>
</div>

<form id="user-contact" class="form-horizontal" action="{$postUrl}" method="post" data-parsley-validate data-parsley-priority-enabled="false" data-parsley-ui-enabled="true">
    <div class="modal-body">

        <div class="form-group">
            <label class="col-sm-2 control-label">{t}To{/t}</label>
            <div class="col-sm-10">
                <p id="contactedUser" class="form-control-static">{t username=$mail->to_user->username lang={$mail->to_user->preferred_language|language}}%1 (speak %2){/t}</p>
            </div>
        </div>

        <div class="form-group">
            <label for="inputSubject" class="col-sm-2 control-label">{t}Subject{/t}</label>
            <div class="col-sm-10">
                <input type="text" class="form-control maxl" id="inputSubject" name="subject" placeholder="{t}Message subject{/t}" maxlength="75" required value="{$mail->subject}">
            </div>
        </div>

        <div class="form-group">
            <label for="inputUsername" class="col-sm-2 control-label">{t}Message{/t}</label>
            <div class="col-sm-10">
                <textarea class="form-control maxl" rows="5" id="message" name="message" placeholder="{t}Message to user{/t}" maxlength="5120" required>{$mail->content}</textarea>
            </div>
        </div>

        {call recaptcha}

    </div>
    <div class="modal-footer">
        <a class="btn btn-default" href="{'user_details'|alias:sprintf('userid=%d', $f3->get('SESSION.CURRENT_USER'))}" title="{t}Back to user page{/t}" data-dismiss="modal">
            {t}Dismiss{/t}
        </a>
        <button type="submit" class="btn btn-info">{t}Send message{/t}</button>
    </div>
</form>
{/block}

{block name=javascript_modal append}
{include 'js/dialogs/dialog_contact_user.tpl.js'}
{/block}
