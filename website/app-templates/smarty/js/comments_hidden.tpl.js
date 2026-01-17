$(document).on("click", "i.move-comment-hidden", function() {
    $(this).parent().find("div.comment-hidden").toggleClass("hidden");
    $(this).toggleClass("hidden");
});
