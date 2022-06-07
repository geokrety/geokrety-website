{extends file='base.tpl'}

{block name=title}{t}GeoKrety generator{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL) && ''}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL) && ''}

{block name=content}
    <h1>{t}GeoKrety generator{/t}</h1>

    <form class="form-horizontal" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{t}General details{/t}</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label for="inputCount" class="col-sm-3 control-label" aria-describedby="helpInputCount">{t}How many GeoKrety to create{/t}</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="inputCount" name="count" step="1" pattern="\d+" min="0" max="{GK_GENERATOR_MAX_COUNT}" value="{if isset($count)}{$count}{/if}" required>
                        <p id="helpInputCount" class="help-block tooltip_large" data-toggle="tooltip">{t limit=GK_GENERATOR_MAX_COUNT}From 1 to %1.{/t}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputOwner" class="col-sm-3 control-label" aria-describedby="helpInputOwner">{t}Owner ID{/t}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="inputOwner" name="owner" value="{if isset($owner)}{$owner}{/if}">
                        <p id="helpInputOwner" class="help-block tooltip_large" data-toggle="tooltip">{t}Leave empty or set 0 to not set any owner.{/t}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label" aria-describedby="helpInputName">{t}GeoKrety name template{/t}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control maxl" id="inputName" name="name" placeholder="{t}GeoKret name{/t}" minlength="{GK_GEOKRET_NAME_MIN_LENGTH}" maxlength="{GK_GEOKRET_NAME_MAX_LENGTH}" value="{if isset($name)}{$name}{/if}" required>
                        <p id="helpInputName" class="help-block tooltip_large" data-toggle="tooltip">{t escape=no link='https://www.php.net/manual/en/function.sprintf.php'}Use php <a href="%1" target="_blank">`sprintf()`</a> format specifiers to replace the increment. ex: "%02d"{/t}</p>
                    </div>

                    <label for="inputNameStartAt" class="control-label col-sm-1">{t}Start at{/t}</label>
                    <div class="col-md-2">
                        <input type="number" class="form-control" id="inputNameStartAt" name="NameStartAt" step="1" pattern="\d+" min="0" value="{if isset($nameStartAt)}{$nameStartAt}{else}1{/if}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputGeokretType" class="col-sm-3 control-label">{t}GeoKret type{/t}</label>
                    <div class="col-sm-9">
                        <select class="form-control" id="inputGeokretType" name="type">
                            {foreach \GeoKrety\GeokretyType::getTypes() as $key => $gktype}
                                <option value="{$key}" {if isset($type) && $type == $key} selected{/if} required>{$gktype}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputMission" class="col-sm-3 control-label">{t}Mission{/t}</label>
                    <div class="col-sm-9">
                        <textarea class="form-control maxl" rows="5" id="inputMission" name="mission" placeholder="{t}What is this GeoKret mission?{/t}" maxlength="5120">{if isset($mission)}{$mission}{/if}</textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputPictures" class="col-sm-3 control-label">{t}Pictures{/t}</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">TODO</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{t}Tracking Code{/t}</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label for="inputTCPrefix" class="col-sm-3 control-label">{t}Tracking Code prefix{/t}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="inputTCPrefix" name="TCPrefix" pattern="{GK_GENERATOR_TRACKING_CODE_PREFIX_REGEX}" value="{if isset($TCPrefix)}{$TCPrefix}{/if}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputTCLength" class="col-sm-3 control-label">{t}Tracking Code length{/t}</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="inputTCLength" name="TCLength" step="1" pattern="\d+" min="{GK_SITE_TRACKING_CODE_LENGTH}" value="{if isset($TCLength)}{$TCLength}{else}6{/if}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputTCAlphabet" class="col-sm-3 control-label">{t}Tracking Code Alphabet{/t}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="inputTCAlphabet" name="TCAlphabet" value="{if isset($TCAlphabet)}{$TCAlphabet}{else}{GK_GENERATOR_TRACKING_CODE_ALPHABET}{/if}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputTCSuffix" class="col-sm-3 control-label">{t}Tracking Code suffix{/t}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="inputTCSuffix" name="TCSuffix" pattern="{GK_GENERATOR_TRACKING_CODE_SUFFIX_REGEX}" value="{if isset($TCSuffix)}{$TCSuffix}{/if}">
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{t}Owner Code{/t}</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label for="inputOCPrefix" class="col-sm-3 control-label">{t}Owner Code prefix{/t}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="inputOCPrefix" name="OCPrefix" value="{if isset($OCPrefix)}{$OCPrefix}{/if}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputOClength" class="col-sm-3 control-label">{t}Owner Code length{/t}</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="inputOClength" name="OClength" step="1" pattern="\d+" min="{GK_SITE_OWNER_CODE_LENGTH}" value="{if isset($OClength)}{$OClength}{else}6{/if}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputOCAlphabet" class="col-sm-3 control-label">{t}Owner Code Alphabet{/t}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="inputOCAlphabet" name="OCAlphabet" value="{if isset($OCAlphabet)}{$OCAlphabet}{else}{GK_GENERATOR_OWNER_CODE_ALPHABET}{/if}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputOCSuffix" class="col-sm-3 control-label">{t}Owner Code suffix{/t}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="inputOCSuffix" name="OCSuffix" value="{if isset($OCSuffix)}{$OCSuffix}{/if}">
                    </div>
                </div>

            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-9">
                <button type="submit" id="buttonGenerate" class="btn btn-primary">{t}Generate{/t}</button>
            </div>
        </div>

    </form>
{/block}

{block name=javascript}
// Bind SimpleMDE editor
var inscrybmde = new InscrybMDE({
    element: $("#inputMission")[0],
    hideIcons: ['side-by-side', 'fullscreen', 'quote'],
    promptURLs: true,
    spellChecker: false,
    status: false,
    forceSync: true,
    renderingConfig: {
        singleLineBreaks: false,
    },
    minHeight: '100px',
});
{if GK_DEVEL}
{* used by Tests-qa in Robot  Framework *}
$("#inputMission").data({ editor: inscrybmde });
{/if}
{/block}
