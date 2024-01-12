/*global modalLoad */

$("#modal").on("show.bs.modal", function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data("type");
    let id = button.data("id");

    if (typeName === "admin-news-view") {
        modalLoad("{'admin_news_view'|alias:'newsid=%ID%'}".replace("%ID%", id));
    }
});
