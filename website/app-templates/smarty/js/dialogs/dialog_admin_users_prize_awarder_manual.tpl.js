$("#modal").on("show.bs.modal", function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data("type");
    let user_id = button.data("user-id");
    let award_id = button.data("award-id");

    if (typeName === "admin-users-award-prize") {
        modalLoad("{'admin_users_prize_awarder_manual'|alias:'userid=%USER_ID%,award_id=%AWARD_ID%'}"
            .replace("%USER_ID%", user_id)
            .replace("%AWARD_ID%", award_id?award_id:'0')
        );
    }

});
$("#modal").on("shown.bs.modal", function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data("type");
    let award_id = button.data("award-id");
    if (typeName === "admin-users-award-prize" && !award_id) {
        $('#award-button').prop("disabled", true);
    }
});
