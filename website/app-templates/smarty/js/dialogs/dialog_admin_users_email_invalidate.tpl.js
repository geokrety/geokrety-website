$("#modal").on("show.bs.modal", function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data("type");
    let id = button.data("id");

    if (typeName === "admin-users-email-invalidate") {
        modalLoad("{'admin_users_email_invalidate'|alias:'userid=%ID%'}".replace("%ID%", id));
    }
});
