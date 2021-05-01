
$('.gallery').each(function() {
    $(this).magnificPopup({
        delegate: 'a.picture-link',
        type: 'image',
        gallery: { enabled: true }
    });
});

$('a.has-gk-avatar').each(function() {
    $(this).magnificPopup({
        type: 'image',
        image: {
          titleSrc: 'title'
        }
    });
});
