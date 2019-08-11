{extends file='base.tpl'}

{block name=css}
<link rel="stylesheet" href="{GK_CDN_LIBRARIES_PARSLEY_CSS_URL}">
<link rel="stylesheet" href="{GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL}"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_PARSLEY_JS_URL}"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL}"></script>
{/block}

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
{/block}
