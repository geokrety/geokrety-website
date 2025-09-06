const $preview = $('#geokretLabelPreview');
const $previewLink = $('#geokretLabelPreviewLink');
const $refreshBtn = $('#refreshLabelPreviewBtn');

const $tpl = $('#inputLabelTemplate');
const $name = $('#inputName');
const $ownerName = $('#inputOwnerName');
const $refNumber = $('#inputReferenceNumber');
const $trackCode = $('#inputTrackingCode');
const $mission = $('#inputMission');
const $helpLangs = $('#inputLabelHelpLanguages');

function getFormValues() {
    return {
        tpl: $tpl.val() || 'default',
        name: $name.val() || '',
        mission: $mission.val() || '',
        helpLangs: $helpLangs.val() || [],
    };
}

new TomSelect($helpLangs, {
    maxItems: 4,
    plugins: ['remove_button'],
});

$previewLink.magnificPopup({
    type: 'image',
    closeOnContentClick: true,
    zoom: {
        enabled: true,
        duration: 300,
    }
});

let currentObjectURL = null;

function setPreviewFromBlob(blob) {
    if (currentObjectURL) URL.revokeObjectURL(currentObjectURL);
    currentObjectURL = URL.createObjectURL(blob);
    $preview.attr('src', currentObjectURL);
    $previewLink.attr('href', currentObjectURL);
}

// build querystring for GET /label/png
function buildQuery() {
    const v = getFormValues();
    const p = new URLSearchParams();
    if (v.name) p.set('name', v.name);
    if (v.mission) p.set('mission', v.mission);
    if (v.tpl) p.set('label_template', v.tpl);
    const langs = Array.isArray(v.helpLangs)
        ? Array.from(new Set(v.helpLangs.map(s => String(s).toLowerCase().trim())))
        : [];
    langs.forEach(l => p.append('helpLanguages[]', l));
    return p;
}

// GET to @geokret_label_png and show
function labelPreview({ bypassCache = false } = { }) {
    const baseUrl = "{'geokret_label_png'|alias:sprintf('@gkid=%s', $geokret->gkid())}";
    const qs = buildQuery();
    if (bypassCache) qs.set('_cb', Date.now()); // optional cache-buster
    const url = baseUrl + '?' + qs.toString();
    $preview.attr('src', url);
    $previewLink.attr('href', url);
}


// initial load
labelPreview();

$refreshBtn.on('click', async function () {
    const $btn = $(this);
    const originalText = $btn.text();
    $btn.prop('disabled', true).text('{t}Refreshing…{/t}');

    try {
        await labelPreview({ bypassCache: false });
    } finally {
        $btn.prop('disabled', false).text(originalText);
    }
});
$tpl.on('change', async function(){
    await labelPreview({ bypassCache: false });
});
