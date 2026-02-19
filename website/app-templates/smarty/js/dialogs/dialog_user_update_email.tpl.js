$("body").on("change", "#instantNotificationsCheckbox", function() {
    const $optionsDiv = $("#instantNotificationsOptions");
    const $checkboxes = $(".instant-notif-checkbox");
console.log("Instant notifications checkbox changed:", $(this).is(":checked"));
    if ($(this).is(":checked")) {
        $optionsDiv.removeClass("hidden");
        // Check all granular checkboxes when enabling instant notifications
        $checkboxes.prop("checked", true);
    } else {
        $optionsDiv.addClass("hidden");
    }
});

// Initialize on page load
$(document).ready(function() {
    $("#instantNotificationsCheckbox").trigger("change");
});
