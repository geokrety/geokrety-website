
if (window.lightbox && typeof lightbox.option === "function") {
    lightbox.option({
        fadeDuration: 0,
        imageFadeDuration: 0,
        resizeDuration: 50,
        albumLabel: "{t}Image %1 of %2{/t}"
    });
}

$(".gallery").each(function (idx) {
    $(this).find("a.picture-link").each(function () {
        $(this).attr("data-lightbox", "gallery-" + idx);
    });
});

function bind_gk_avatars_buttons() {
    $("a.has-gk-avatar").each(function(idx) {
        $(this).attr("data-lightbox", "gk-avatar-" + idx);
    });
}
bind_gk_avatars_buttons()
