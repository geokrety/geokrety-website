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

const HAS_GEOKRET = {if isset($geokret) && $geokret->id}true{else}false{/if};
const LABEL_PNG_BASE = "{if isset($geokret)}{'geokret_label_png'|alias:sprintf('@gkid=%s', $geokret->gkid())}{/if}";
const CDN_SCREENSHOTS_BASE = "{GK_CDN_LABELS_SCREENSHOTS_URL}";

function getFormValues() {
    return {
        tpl: $tpl.val() || 'default',
        name: $name.val() || '',
        mission: $mission.val() || '',
        helpLangs: $helpLangs.val() || [],
    };
}

if ($helpLangs.length) {
    new TomSelect($helpLangs, { maxItems: 4, plugins: ['remove_button'] });
}

$previewLink.magnificPopup({
    type: 'image',
    closeOnContentClick: true,
    zoom: { enabled: true, duration: 300 }
});

let currentObjectURL = null;
function setPreviewFromBlob(blob) {
    if (currentObjectURL) URL.revokeObjectURL(currentObjectURL);
    currentObjectURL = URL.createObjectURL(blob);
    $preview.attr('src', currentObjectURL);
    $previewLink.attr('href', currentObjectURL);
}

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

// preload helper that resolves when the image finishes loading
let loadSeq = 0;
function loadIntoPreview(url) {
    const seq = ++loadSeq;
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.decoding = 'async';
        img.onload = () => {
            if (seq === loadSeq) {
                $preview.attr('src', url);
                $previewLink.attr('href', url);
            }
            resolve();
        };
        img.onerror = (e) => {
            if (seq === loadSeq) {
                $previewLink.attr('href', url);
            }
            reject(e);
        };
        img.src = url;
    });
}

function labelPreview({ bypassCache = false } = {}) {
    if (HAS_GEOKRET) {
        const qs = buildQuery();
        if (bypassCache) qs.set('_cb', Date.now());
        const q = qs.toString();
        const url = q ? LABEL_PNG_BASE + '?' + q : LABEL_PNG_BASE;
        return loadIntoPreview(url);
    } else {
        const tpl = ($tpl.val() || 'default').toString();
        const url = CDN_SCREENSHOTS_BASE + '/' + encodeURIComponent(tpl) + '.png';
        return loadIntoPreview(url);
    }
}

labelPreview().catch(()=>{});

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
$tpl.on('change', async function () {
    try { await labelPreview({ bypassCache: false }); } catch {}
});
