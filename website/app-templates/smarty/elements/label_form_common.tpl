
<div class="form-group">
    <label for="inputLabelTemplate" class="col-sm-2 control-label">{t}Label template{/t}</label>
    <div class="col-sm-10">
        <select class="form-control" id="inputLabelTemplate" name="label_template">
            {foreach $templates as $template}
                <option value="{$template->template}" {if isset($geokret) and $geokret->label_template and $geokret->label_template->id === $template->id} selected{/if}>{$template->title}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="form-group">
    <label for="inputLabelHelpLanguages" class="col-sm-2 control-label">{t}Label help languages{/t}</label>
    <div class="col-sm-10">
        <select class="form-control" id="inputLabelHelpLanguages" name="label_languages[]" autocomplete="off" multiple>
            {foreach $languages as $code => $lang}
                {if $code != 'en'}<option value="{$code}"{if isset($geokret) and !is_null($geokret->label_languages) && in_array($code, $geokret->label_languages)} selected{/if}>{$lang}</option>{/if}
            {/foreach}
        </select>
        <span class="help-block">
            {t}Note: not all label templates support this feature and when supported, english is always present.{/t}
        </span>
    </div>
</div>

<div class="form-group">
    <label for="fit_to_page_width" class="col-sm-2 control-label">{t}Label Size{/t}</label>
    <div class="col-sm-10">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="fit_to_page_width" id="fit_to_page_width" value="1" />
                {t}Fit label to page width (100%){/t}
            </label>
        </div>
        <p class="help-block">{t}If checked, the label will be scaled to fit 100% of the page width. If unchecked, the label will be printed at its default size.{/t}</p>
    </div>
</div>

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {call csrf}
        <button type="button" id="refreshLabelPreviewBtn" class="btn btn-default">{t}Refresh preview{/t}</button>
        {block name=label_custom_buttons}{/block}
    </div>
</div>
