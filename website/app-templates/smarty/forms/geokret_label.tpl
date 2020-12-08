<div class="panel panel-default">
    <div class="panel-heading">
         <h3 class="panel-title">{t}Generate a GeoKret Label{/t}</h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
            <div class="form-group">
                <label for="inputGeokretName" class="col-sm-2 control-label">{t}GeoKret name{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control maxl" id="inputGeokretName" name="name" placeholder="{t}GeoKret name{/t}" minlength="{GK_GEOKRET_NAME_MIN_LENGTH}" maxlength="{GK_GEOKRET_NAME_MAX_LENGTH}" required value="{if isset($geokret)}{$geokret->name}{/if}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="inputGeokretOwnerName" class="col-sm-2 control-label">{t}Owner{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputGeokretOwnerName" name="ownerName" placeholder="{t}Owner{/t}" required value="{if isset($geokret)}{$geokret->owner->username}{/if}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="inputGeokretReferenceNumber" class="col-sm-2 control-label">{t}Reference numbers{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputGeokretReferenceNumber" name="referenceNumber" placeholder="{t}Reference number{/t}" required value="{if isset($geokret)}{$geokret->gkid}{/if}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="inputGeokretTrackingCode" class="col-sm-2 control-label">{t}Reference numbers{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputGeokretTrackingCode" name="trackingCode" placeholder="{t}Tracking Code{/t}" required value="{if isset($geokret)}{$geokret->tracking_code}{/if}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="inputMission" class="col-sm-2 control-label">{t}Mission{/t}</label>
                <div class="col-sm-10">
                    <textarea class="form-control maxl" rows="5" id="inputMission" name="mission" placeholder="{t}What is this GeoKret mission?{/t}" maxlength="5120">{if isset($geokret)}{$geokret->mission|markdown:text}{/if}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="inputLabelTemplate" class="col-sm-2 control-label">{t}Label template{/t}</label>
                <div class="col-sm-10">
                    <select class="form-control" id="inputLabelTemplate" name="labelTemplate">
                        {foreach $templates as $template}
                        <option value="{$template->template}" {if $geokret->label_template && $geokret->label_template->id === $template->id} selected{/if}>{$template->title}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="inputLabelHelpLanguages" class="col-sm-2 control-label">{t}Label help languages{/t}</label>
                <div class="col-sm-10">
                    <select class="form-control" id="inputLabelHelpLanguages" name="helpLanguages[]" multiple>
                        {foreach $languages as $code => $lang}
                            {if $code != 'en'}<option value="{$code}"{if !is_null($selectedLanguages) && in_array($code, $selectedLanguages)} selected{/if}>{$lang}</option>{/if}
                        {/foreach}
                    </select>
                    <span class="help-block">
                        {t}Note1: not all label templates support this feature and when supported, english is always present.{/t}
                    </span>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary" id="generateAsPng" name="generateAsPng">{t}Generate as .png{/t}</button>
                    <button type="submit" class="btn btn-primary" id="generateAsSvg" name="generateAsSvg">{t}Generate as .svg{/t}</button>
                    <button type="submit" class="btn btn-primary" id="generateAsPdf" name="generateAsPdf">{t}Generate as .pdf{/t}</button>
                </div>
            </div>

        </form>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
         <h3 class="panel-title">{t}GeoKret label preview{/t}</h3>
    </div>
    <div class="panel-body">
        <a id="geokretLabelPreviewLink" href="" class="picture-link" title="{t}GeoKret label preview{/t}">
            <img id="geokretLabelPreview" class="img-responsive center-block" alt="{t}GeoKret label preview{/t}">
        </a>
    </div>
</div>
