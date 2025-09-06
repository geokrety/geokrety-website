{extends file='base.tpl'}

{block name=title}{t}Label generator{/t}{/block}

{\GeoKrety\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL) && ''}
{\GeoKrety\Assets::instance()->addCss(GK_CDN_TOM_SELECT_CSS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_TOM_SELECT_JS) && ''}

{block name=label_custom_buttons}
    <button type="submit" class="btn btn-primary" id="generateAsPng" name="generateAsPng">{t}Generate as .png{/t}</button>
    <button type="submit" class="btn btn-primary" id="generateAsSvg" name="generateAsSvg">{t}Generate as .svg{/t}</button>
    <button type="submit" class="btn btn-primary" id="generateAsPdf" name="generateAsPdf">{t}Generate as .pdf{/t}</button>
{/block}

{block name=content}
<h1>{t}Label generator{/t}</h1>
{include file='forms/geokret_label.tpl'}
{/block}

{block name=javascript}

    // Bind SimpleMDE editor
    var inscrybmde = new InscrybMDE({
    element: $("#inputMission")[0],
    hideIcons: ['side-by-side', 'fullscreen', 'quote'],
    autoDownloadFontAwesome: false,
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
// Bind label preview
{include 'js/_label_preview.js'}

{/block}
