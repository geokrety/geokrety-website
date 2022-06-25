$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget)
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName === 'user-contact') {
        modalLoad("{'mail_to_user'|alias:'userid=%ID%'}".replace('%ID%', id), bindControls);
    }

    if (typeName === 'user-contact-by-geokret') {
        modalLoad("{'mail_by_geokret'|alias:'gkid=%ID%'}".replace('%ID%', id), bindControls);
    }
});

function bindControls() {
    $('#user-contact').parsley();
    // Bind SimpleMDE editor
    inscrybmde = new InscrybMDE({
        element: $("#message")[0],
        hideIcons: ['side-by-side', 'fullscreen', 'quote', 'image'],
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
    grecaptcha.render("recaptcha_wrapper", {
        sitekey: "{GK_GOOGLE_RECAPTCHA_PUBLIC_KEY}",
        theme: "light"
    });
}


$('body').on('submit', '#user-contact', function(event) {
    let spinner = new Spinner({
        lines: 15,
        length: 40,
        width: 11,
        radius: 32,
        scale:1.55,
        color: "#2ab2c2",
    }).spin(document.getElementById('spinner-center'));
});
