// ----------------------------------- JQUERY - RUCHY INVENTORY - BEGIN

// bind nrSearchButton
$("#nrSearchButton").bind("click", function() {
    $("#nr").parsley().validate();
});

//
function toggleAlertMaxGKReached(el) {
    var count = $("#geokretyListTable [name*=\"geokretySelected\"]:checkbox:checked").length;
    if (count >= {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
        $("#geokretySelectAll").prop("checked", false);
        showAlertMaxGKReached(true);
    } else {
        showAlertMaxGKReached(false);
    }
    $("#modalInventorySelectButton span.badge").text(count);
}

// change header checkbox
$("body").on("change", "#geokretySelectAll", function() {
    var checked = $(this).is(":checked");
    if (checked) {
        var inventory = $("#geokretyListTable tr:not(.hidden) [name*=\"geokretySelected\"]").slice(0, {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS});
        inventory.each(function() {
            if ($("#geokretyListTable [name*=\"geokretySelected\"]:checkbox:checked").length >= {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
                $(this).prop("checked", false);
                return false;
            }
            this.checked = checked;
        })
    } else {
        $("#geokretyListTable tr [name*=\"geokretySelected\"]").each(function() {
            $(this).prop("checked", false);
        })
    }
    toggleAlertMaxGKReached();

// change one GeoKret checkbox
}).on("change", "#geokretyListTable [name*=\"geokretySelected\"]", function() {
    var inventory = $("#geokretyListTable [name*=\"geokretySelected\"]:checkbox:checked");
    if (inventory.length > {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
        $("#geokretySelectAll").prop("checked", false);
        $(this).prop("checked", false);
    }
    toggleAlertMaxGKReached();

// GK specific choose button
}).on("click", "#geokretyListTable [name*=\"btnChooseGK\"]", function(event) {
    event.preventDefault();
    var inventory = $("#geokretyListTable [name*=\"geokretySelected\"]:checkbox:checked");
    if (inventory.length <= {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
        var trackingCode = $(this).data("trackingcode");
        fillTrackingCode([trackingCode]);
    }

// Select button from the modal
}).on("click", "#modalInventorySelectButton", function() {
    var trackingCodes = Array();
    $("#geokretyListTable [name*=\"geokretySelected\"]:checkbox:checked").each(function() {
        trackingCodes.push($(this).data("trackingcode"));
    })
    fillTrackingCode(trackingCodes);

// Filter the inventory
}).on("keyup", "#gk-filter", function() {
    var filter = $("#gk-filter").val().toLowerCase();
    $("#geokretyListTable .gk-name").each(function() {
        var title = $(this).attr("title");
        var gkid = $(this).text();
        var tr = $(this).closest("tr");
        if (~latinize(title.toLowerCase()).indexOf(latinize(filter)) || ~latinize(gkid.toLowerCase()).indexOf(latinize(filter))) {
            tr.removeClass("hidden");
        } else {
            tr.addClass("hidden");
        }
    });
    // var inventory = $("#geokretyListTable [name*=\"geokretySelected\"]");
    if ($("#geokretyListTable tr").length > {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
        $("#geokretySelectAll").prop("checked", false);
    }
})

// Check by already added GK
function checkAlreadyAddedTrackingCode() {
  const ts = getTS();
  let codes = [];

  if (ts) {
    const v = ts.getValue();         // array for multi, string for single
    codes = Array.isArray(v) ? v : (v ? [v] : []);
  } else {
    codes = ($("#nr").val() || "").split(",").map(norm).filter(Boolean);
  }

  codes.forEach(function (item) {
    $("#geokretyListTable input[data-trackingcode]").filter(function () {
      return String(this.getAttribute("data-trackingcode")).toUpperCase() === item;
    }).prop("checked", true);
  });

  if (typeof toggleAlertMaxGKReached === "function") {
    toggleAlertMaxGKReached();
  }
}

// Add a tracking code to the list
function fillTrackingCode(trackingCodes) {
  const ts = getTS();

  const raw = trackingCodes || [];
  const codes = raw.map(norm).filter(Boolean);

  if (ts) {
    codes.forEach((code) => {
      if (!ts.options[code]) {
        ts.addOption({ tracking_code: code, label: code });
      }
      ts.addItem(code, true);
    });
    $("#nr").parsley().reset();
    $("#modal").modal("hide");
    return;
  }

  // Fallback: plain input as CSV
  const current = $("#nr").val();
  const merged = [current, codes.join(",")].filter(Boolean).join(",");
  $("#nr").parsley().reset();
  $("#nr").val(merged).trigger("focusout");
  $("#modal").modal("hide");
}

// Show/hide warning message
function showAlertMaxGKReached(show) {
    if (show) {
        $("#maxGKSelctionReached").show().removeClass("hidden");
    } else {
        $("#maxGKSelctionReached").hide();
    }
}

// ----------------------------------- JQUERY - RUCHY INVENTORY - END
