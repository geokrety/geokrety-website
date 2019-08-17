$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');
    var id = button.data('id');

    if (typeName == 'user-contact') {
        $(this).find('.modal-content').load("{'mail_to_user'|alias:'userid=%ID%'}".replace('%ID%', id), function() { bindControls(); });
    }

    if (typeName == 'user-contact-by-geokret') {
        $(this).find('.modal-content').load("{'mail_by_geokret'|alias:'gkid=%ID%'}".replace('%ID%', id), function() { bindControls(); });
    }
});

$('#modal').on('hide.bs.modal', function(event) {
    $('#recaptcha_wrapper').empty();
});

function bindControls() {
    $('#user-contact').parsley();
    // Bind SimpleMDE editor
    inscrybmde = new InscrybMDE({
        element: $("#message")[0],
        hideIcons: ['side-by-side', 'fullscreen', 'quote', 'image'],
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
};


$('body').on('submit', '#user-contact', function(event) {
    var spinner = new Spinner({
        lines: 15,
        length: 40,
        width: 11,
        radius: 32,
        scale:1.55,
        color: "#2ab2c2",
    }).spin(document.getElementById('spinner-center'));
});
