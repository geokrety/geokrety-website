
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

function bind_gk_avatars_buttons() {
    $('a.has-gk-avatar').each(function() {
        $(this).magnificPopup({
            type: 'image',
            image: {
              titleSrc: 'title'
            }
        });
    });
}
bind_gk_avatars_buttons()
