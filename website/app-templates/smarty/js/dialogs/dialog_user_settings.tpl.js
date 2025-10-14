$("#modal").on("show.bs.modal", function(event) {
    const button = $(event.relatedTarget);
    const typeName = button.data("type");

    // Handle edit setting modal
    if (typeName === "user-setting-edit") {
        const settingName = button.data("setting-name");
        const settingDescription = button.data("setting-description");
        const settingType = button.data("setting-type");
        const settingCurrent = button.data("setting-current");

        modalLoad("{'user_setting_edit_dialog'|alias}", function() {
            $("#editSettingName").text(settingName);
            $("#editSettingDescription").text(settingDescription);

            let inputField = "";

            if (settingType === "bool" || settingType === "boolean") {
                const trueSelected = (settingCurrent === "true" || settingCurrent === true) ? " selected" : "";
                const falseSelected = (settingCurrent === "false" || settingCurrent === false) ? " selected" : "";
                inputField = "<select id=\"userSettingValue\" class=\"form-control\"><option value=\"true\"" + trueSelected + ">✓ True</option><option value=\"false\"" + falseSelected + ">✗ False</option></select>";
            } else if (settingType && settingType.indexOf("enum:") === 0) {
                const enumValues = settingType.substring(5).split("|");
                let options = "";
                for (let i = 0; i < enumValues.length; i++) {
                    const val = enumValues[i];
                    const selected = (settingCurrent === val) ? " selected" : "";
                    options += "<option value=\"" + val + "\"" + selected + ">" + val + "</option>";
                }
                inputField = "<select id=\"userSettingValue\" class=\"form-control\">" + options + "</select>";
            } else {
                inputField = "<input type=\"text\" id=\"userSettingValue\" class=\"form-control\" value=\"" + (settingCurrent || "") + "\">";
            }

            $("#editSettingInputContainer").html(inputField);

            $("#saveUserSettingBtn").data("setting-name", settingName);
            $("#saveUserSettingBtn").data("setting-type", settingType);
        });
    }

    // Handle reset setting modal
    if (typeName === "user-setting-reset") {
        const settingName = button.data("setting-name");
        const settingDescription = button.data("setting-description");
        const settingDefault = button.data("setting-default");
        const settingCurrent = button.data("setting-current");

        modalLoad("{'user_setting_reset_dialog'|alias}", function() {
            $("#resetSettingName").text(settingName);
            $("#resetSettingDescription").text(settingDescription);
            $("#resetSettingDefault").text(settingDefault);
            $("#resetSettingCurrent").text(settingCurrent);

            $("#confirmResetSettingBtn").data("setting-name", settingName);
        });
    }
});
