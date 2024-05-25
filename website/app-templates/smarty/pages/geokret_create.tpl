{extends file='base.tpl'}

{block name=title}{t}Create a new GeoKret{/t}{/block}

{\GeoKrety\Assets::instance()->addCss(GK_CDN_BOOTSTRAP_DATETIMEPICKER_CSS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_DATETIMEPICKER_JS) && ''}
{\GeoKrety\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL) && ''}

{block name=content}
{include 'forms/geokret.tpl'}
{/block}

{block name=javascript}
{include 'js/parsley/datebeforenow.js'}

{if isset($geokret) and $geokret->gkid()}
// Bind datepicker
moment.locale('{\Multilang::instance()->current}')
$("#datetimepicker").datetimepicker({
    collapse: true,
    showTodayButton: true,
    locale: moment.locale()
});
$("#born_on_datetime_localized").click(function() {
    $("#datetimepicker").data("DateTimePicker").show();
});
// Initialize date time
$("#datetimepicker").data("DateTimePicker").date(moment.utc("{$geokret->born_on_datetime->format('c')}"));
$("#born_on_datetime").val($("#datetimepicker").data("DateTimePicker").viewDate().format());
$("#born_on_datetime_localized").on("focusout", function(e) {
    $("#born_on_datetime").val($("#datetimepicker").data("DateTimePicker").viewDate().format());
});
{/if}

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
