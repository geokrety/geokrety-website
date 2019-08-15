{block name=css}
<link rel="stylesheet" href="{GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL}"></script>
<script type="text/javascript" src="{GK_GOOGLE_RECAPTCHA_JS_URL}"></script>
{/block}

{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Contact user{/t}</h4>
</div>

<form id="user-contact" class="form-horizontal" action="{'mail_to_user'|alias}" method="post" data-parsley-validate data-parsley-priority-enabled="false" data-parsley-ui-enabled="true">
    <div class="modal-body">

        <div class="form-group">
            <label class="col-sm-2 control-label">{t}To{/t}</label>
            <div class="col-sm-10">
                <p class="form-control-static">{t username=$userTo->username lang={$userTo->preferred_language|language}}%1 (speak %2){/t}</p>
            </div>
        </div>

        <div class="form-group">
            <label for="inputSubject" class="col-sm-2 control-label">{t}Subject{/t}</label>
            <div class="col-sm-10">
                <input type="text" class="form-control maxl" id="inputSubject" name="subject" placeholder="{t}Message subject{/t}" maxlength="75" required value="{if isset($smarty.post.subject)}{$smarty.post.subject}{else if isset($subject)}{$subject}{/if}">
            </div>
        </div>

        <div class="form-group">
            <label for="inputUsername" class="col-sm-2 control-label">{t}Message{/t}</label>
            <div class="col-sm-10">
                <textarea class="form-control maxl" rows="5" id="message" name="message" placeholder="{t}Message to user{/t}" maxlength="5120" required>{if isset($smarty.post.message)}{$smarty.post.message}{/if}</textarea>
            </div>
        </div>

        {if GK_GOOGLE_RECAPTCHA_PUBLIC_KEY}
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <div class="g-recaptcha" data-sitekey="{GK_GOOGLE_RECAPTCHA_PUBLIC_KEY}" id="recaptcha_wrapper"></div>
            </div>
        </div>
        {/if}

    </div>
    <div class="modal-footer">
        <a class="btn btn-default" href="{'user_details'|alias:sprintf('userid=%d', $f3->get('SESSION.CURRENT_USER'))}" title="{t}Back to user page{/t}" data-dismiss="modal">
            {t}Dismiss{/t}
        </a>
        <button type="submit" class="btn btn-info">{t}Send message{/t}</button>
    </div>
</form>
{/block}

{block name=javascript}
$('#user-contact').parsley();

// Bind SimpleMDE editor
var inscrybmde = new InscrybMDE({
    element: $("#message")[0],
    hideIcons: ['side-by-side', 'fullscreen', 'quote', 'image'],
    promptURLs: true,
    spellChecker: false,
    status: false,
    forceSync: true,
    renderingConfig: {
        singleLineBreaks: false,
    },
    minHeight: '100px',
});
{/block}
