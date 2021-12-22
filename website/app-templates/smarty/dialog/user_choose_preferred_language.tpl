{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Choose your preferred language{/t}</h4>
</div>

<form name="languageChange" action="{'user_language_chooser'|alias}" method="post">
    <div class="modal-body">

        <div class="form-group">
            <label for="inputLanguage" class="col-sm-2 control-label">{t}Language{/t}</label>
            <div class="col-sm-10">
                <select class="form-control" id="inputLanguage" name="language">
                    {foreach $languages as $code => $lang}
                    <option value="{$code}" {if $currentUser->preferred_language === $code} selected{/if}>{$lang}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <em>{t}This will be the default language when you log in and the main language in the emails you may receive.{/t}</em>

    </div>
    <div class="modal-footer">
        {call csrf}
        <a class="btn btn-default" href="{'user_details'|alias:sprintf('userid=%d', $currentUser->id)}" title="{t}Back to user page{/t}" data-dismiss="modal">
            {t}Dismiss{/t}
        </a>
        <button type="submit" class="btn btn-info">{t}Change{/t}</button>
    </div>
</form>
{/block}
