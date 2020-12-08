
$('.gallery').each(function() {
    $(this).magnificPopup({
        delegate: 'a.picture-link',
        type: 'image',
        gallery: { enabled:true }
    });
});
