{extends file='base.tpl'}

{block name=title}{t}Create a new GeoKret{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL) && ''}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL) && ''}

{block name=content}
{include 'forms/geokret.tpl'}
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

    let preview = $('#geokretLabelPreview');
    let previewLink = $('#geokretLabelPreviewLink');
    let template = $("#inputLabelTemplate").val();

    // Load on page load
    labelPreview();

    $('#inputLabelTemplate').on('change', function(){
        labelPreview();
    });

    $('#geokretLabelPreviewLink').magnificPopup({
		type: 'image',
		closeOnContentClick: true,
		zoom: {
			enabled: true,
			duration: 300
		}
	});

    function labelPreview() {
        let template = $("#inputLabelTemplate").val();
        let url = "{GK_CDN_LABELS_SCREENSHOTS_URL}/"+template+".png";
        preview.attr("src", url);
        previewLink.attr("href", url);
    }

    // Bind modal
    {include 'js/dialogs/dialog_view_geokrety_legacy_mission.tpl.js'}

{/block}
