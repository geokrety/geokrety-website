{if GK_PIWIK_ENABLED}
$("#checkboxTrackingOptOut").on("change", function () {
    let checked = $(this).prop('checked');

    $.ajax({
        url: "{'user_setting_update'|alias}",
        data: {
            "TRACKING_OPT_OUT": checked,
        },
        type: 'POST',
        dataType: 'json',
    })
    .done(function (data) {
        console.log('success:', data);
        // TODO give user some visual feedback
    })
    .fail(function (data) {
        console.log('error:', data);
        // TODO give user some visual feedback
    });
});
{/if}
