
$('.gallery').each(function() {
    $(this).magnificPopup({
        delegate: 'a.picture-link',
        type: 'image',
        gallery: { enabled: true },
        image: {
            titleSrc: function(item) {
                return $(item.el).parents("figure").find("figcaption").text();
            }
        }
    });
});
