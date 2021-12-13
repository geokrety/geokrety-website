{extends file='base.tpl'}

{block name=title}{t}Label generator{/t}{/block}
{include file='macros/csrf.tpl'}

{block name=content}
<h1>{t}Label generator{/t}</h1>
{include file='forms/geokret_label.tpl'}
{/block}

{block name=javascript}
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

{/block}
