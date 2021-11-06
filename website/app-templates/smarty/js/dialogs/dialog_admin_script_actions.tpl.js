$("#modal").on("show.bs.modal", function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data("type");
    let id = button.data("script-id");

    if (typeName === "admin-script-unlock") {
        modalLoad("{'admin_scripts_unlock'|alias:'scriptid=%ID%'}".replace("%ID%", id));
    } else if (typeName === "admin-script-ack") {
        modalLoad("{'admin_scripts_ack'|alias:'scriptid=%ID%'}".replace("%ID%", id));
    }
});
