$("#modal").on("show.bs.modal", function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data("type");
    let key = button.data("key");
    let name = button.data("name");

    if (typeName === "admin-rate-limit-reset") {
        modalLoad("{'admin_rate_limit_reset'|alias:'key=%KEY%,name=%NAME%'}".replace("%KEY%", key).replace("%NAME%", name));
    }
});
