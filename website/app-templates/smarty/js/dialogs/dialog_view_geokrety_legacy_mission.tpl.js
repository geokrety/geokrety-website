$("#modal").on("show.bs.modal", function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data("type");
    let id = button.data("id");

    if (typeName === "geokret-legacy-mission") {
        modalLoad("{'geokrety_legacy_mission'|alias:'gkid=%ID%'}".replace("%ID%", id));
    }
});
