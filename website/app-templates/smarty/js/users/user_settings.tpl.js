// Translations
const i18nUserCustomSettings = {
    editButton: "{t}Edit{/t}",
    resetButton: "{t}Reset{/t}",
    saveButton: "{t}Save{/t}",
    cancelButton: "{t}Cancel{/t}",
    customized: "{t}Customized{/t}",
    settingUpdated: "{t}Setting updated successfully{/t}",
    settingReset: "{t}Setting reset to default{/t}",
    errorSaving: "{t}Error saving setting{/t}",
    confirmReset: "{t}Are you sure you want to reset this setting to its default value?{/t}",
    editSetting: "{t}Edit Setting{/t}",
    currentValue: "{t}Current Value{/t}",
    closeButton: "{t}Close{/t}",
    boolTrue: "{t}True{/t}",
    boolFalse: "{t}False{/t}"
};

//{literal}
// Get settings data from Smarty
const userCustomSettingsData = {/literal}{$settingsData|@json_encode nofilter}{literal};

// Format value for display
function formatUserSettingValue(value) {
    if (value === "true" || value === true) return "✓";
    if (value === "false" || value === false) return "✗";
    return value || "-";
}

// Parse enum type definition (e.g., "enum:metric|imperial")
function parseEnumType(type) {
    if (!type || !type.startsWith("enum:")) return null;
    const values = type.substring(5).split("|");
    return values;
}

// Update DataTable row with new values
function updateTableRow(settingName, newValue, isCustomized) {
    userCustomSettingsTable.rows().every(function() {
        const data = this.data();
        if (data.name === settingName) {
            data.current = newValue;
            data.is_customized = isCustomized;
            this.data(data).draw(false);
            return false;
        }
    });
}

// Save setting via API
function saveUserSetting(name, value, type) {
    const data = {};
    data[name] = value;

    $.ajax({
        url: "{/literal}{'user_setting_update'|alias}{literal}",
        method: "POST",
        data: data,
        dataType: "json",
        success: function(response) {
            $("#modal").modal("hide");
            GK.flashMessage(i18nUserCustomSettings.settingUpdated, "success", 3000);
            updateTableRow(name, value, true);
        },
        error: function(xhr) {
            let errorMsg = i18nUserCustomSettings.errorSaving;
            if (xhr.responseJSON && xhr.responseJSON.length > 0) {
                errorMsg = xhr.responseJSON.join("<br>");
            }
            GK.flashMessage(errorMsg, "danger");
        }
    });
}

// Reset setting to default
function resetUserSetting(name) {
    let defaultValue = null;
    userCustomSettingsTable.rows().every(function() {
        const data = this.data();
        if (data.name === name) {
            defaultValue = data.default;
            return false;
        }
    });

    const data = {};
    data[name] = defaultValue;

    $.ajax({
        url: "{/literal}{'user_setting_update'|alias}{literal}",
        method: "POST",
        data: data,
        dataType: "json",
        success: function(response) {
            GK.flashMessage(i18nUserCustomSettings.settingReset, "success", 3000);
            updateTableRow(name, defaultValue, false);
        },
        error: function(xhr) {
            GK.flashMessage(i18nUserCustomSettings.errorSaving, "danger");
        }
    });
}

// Initialize DataTable (using jQuery plugin syntax for DataTables 1.12.1)
const userCustomSettingsTable = $("#userCustomSettingsTable").DataTable({
    data: userCustomSettingsData,
    columns: [
        {
            data: "name",
            render: function(data, type, row) {
                const badge = row.is_customized ? " <span class=\"badge badge-info\">" + i18nUserCustomSettings.customized + "</span>" : "";
                return data + badge;
            }
        },
        { data: "description" },
        {
            data: "default",
            render: function(data) {
                return formatUserSettingValue(data);
            }
        },
        {
            data: "current",
            render: function(data, type, row) {
                const className = row.is_customized ? "text-primary font-weight-bold" : "";
                return "<span class=\"" + className + "\">" + formatUserSettingValue(data) + "</span>";
            }
        },
        {
            data: null,
            orderable: false,
            render: function(data, type, row) {
                const editBtn = "<button class=\"btn btn-sm btn-primary\" data-toggle=\"modal\" data-target=\"#modal\" data-type=\"user-setting-edit\" data-setting-name=\"" + row.name + "\" data-setting-description=\"" + (row.description || "") + "\" data-setting-type=\"" + row.type + "\" data-setting-current=\"" + row.current + "\">" + i18nUserCustomSettings.editButton + "</button> ";
                const resetBtn = row.is_customized ? "<button class=\"btn btn-sm btn-warning\" data-toggle=\"modal\" data-target=\"#modal\" data-type=\"user-setting-reset\" data-setting-name=\"" + row.name + "\" data-setting-description=\"" + (row.description || "") + "\" data-setting-default=\"" + formatUserSettingValue(row.default) + "\" data-setting-current=\"" + formatUserSettingValue(row.current) + "\">" + i18nUserCustomSettings.resetButton + "</button>" : "";
                return editBtn + resetBtn;
            }
        }
    ],
    order: [[0, "asc"]],
    pageLength: 25
});

// Handle save from edit modal (form submission)
$("#modal").on("submit", "#editUserSettingForm", function(e) {
    e.preventDefault();
    const settingName = $("#saveUserSettingBtn").data("setting-name");
    const settingType = $("#saveUserSettingBtn").data("setting-type");
    const value = $("#userSettingValue").val();
    saveUserSetting(settingName, value, settingType);
    return false;
});

// Handle reset confirmation from modal
$("#modal").on("click", "#confirmResetSettingBtn", function() {
    const settingName = $(this).data("setting-name");
    resetUserSetting(settingName);
    $("#modal").modal("hide");
});
//{/literal}
