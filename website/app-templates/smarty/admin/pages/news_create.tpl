{extends file='base.tpl'}

{block name=title}{t}Create News{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL) && ''}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL) && ''}

{block name=content}
{include 'forms/admin_news.tpl'}
{/block}


{block name=javascript}
// Bind SimpleMDE editor
var inscrybmde = new InscrybMDE({
    element: $("#content")[0],
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
$("#content").data({ editor: inscrybmde });
{/if}
{/block}
