{extends file='base.tpl'}

{block name=title}{t}Create a new GeoKret{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL)}

{block name=content}
{include 'forms/geokret.tpl'}
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
