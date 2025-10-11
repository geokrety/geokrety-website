// ===================================
// QR Code Scanner Module
// ===================================

(function() {
    "use strict";

    // Translations
    const i18n = {
        success: "{t}Success!{/t}",
        trackingCode: "{t}Tracking code{/t}",
        scanned: "{t}Scanned{/t}",
        duplicate: "{t}Duplicate{/t}",
        error: "{t}Error:{/t}",
        failedToAdd: "{t}Failed to add tracking code to field{/t}",
        invalidQr: "{t}Invalid QR code format. Expected GeoKrety URL.{/t}",
        cameraError: "{t}Failed to access camera. Please check permissions.{/t}",
        maxReached: "{t}Maximum number of codes reached{/t}",
        scanAnother: "{t}Scan another code or click Finish{/t}",
        remaining: "{t}remaining{/t}",
        modalTitle: "{t}Scan QR Code{/t}",
        scannedCodesTitle: "{t}Scanned Codes{/t}",
        helpText: "{t}Point your camera at a GeoKrety QR code{/t}",
        finishButton: "{t}Finish Scanning{/t}",
        cancelButton: "{t}Cancel{/t}"
    };

//{literal}
    const MODAL_SELECTOR = "#modal";
    const MODAL_CONTENT_SELECTOR = MODAL_SELECTOR + " .modal-content";
    const QR_MODAL_EVENT_NAMESPACE = ".qrScanner";

    // Configuration
    const QR_CONFIG = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };

    // QR code URL patterns for GeoKrety
    const QR_PATTERNS = [
        /[?&]nr=([A-Z0-9]+)/i,              // /m/qr.php?nr=XYZXYZ
        /[?&]tracking_code=([A-Z0-9]+)/i    // /moves?tracking_code=XYZXYZ
    ];

    // State
    let html5QrCode = null;
    let isScanning = false;
    let scannedCodes = [];  // Array to track scanned codes in current session
    let maxItems = 1;       // Will be set from TomSelect config

    /**
     * Extract tracking code from QR code URL
     * @param {string} decodedText - The decoded QR code text
     * @returns {string|null} - Extracted tracking code or null
     */
    function extractTrackingCode(decodedText) {
        if (!decodedText || typeof decodedText !== "string") {
            return null;
        }

        // Try each pattern
        for (const pattern of QR_PATTERNS) {
            const match = decodedText.match(pattern);
            if (match && match[1]) {
                return match[1].toUpperCase().trim();
            }
        }

        return null;
    }

    /**
     * Get max items allowed from TomSelect configuration
     * @returns {number} - Maximum number of items allowed
     */
    function getMaxItems() {
        const ts = getTS();
        if (!ts || !ts.settings) {
            return 1; // Default to 1 if not available
        }
        return ts.settings.maxItems || 1;
    }

    /**
     * Check if code was already scanned in this session
     * @param {string} trackingCode - The tracking code to check
     * @returns {boolean} - True if already scanned
     */
    function isAlreadyScanned(trackingCode) {
        return scannedCodes.includes(trackingCode);
    }

    /**
     * Add tracking code to scanned list
     * @param {string} trackingCode - The tracking code to add
     * @param {boolean} isDuplicate - Whether this is a duplicate scan
     */
    function addToScannedList(trackingCode, isDuplicate) {
        if (!isDuplicate) {
            scannedCodes.push(trackingCode);
        }

        const $list = $("#qr-codes-list");
        const $panel = $("#qr-scanned-list");
        const $count = $("#qr-scan-count");

        // Show panel if hidden
        $panel.show();

        // Add to list with appropriate styling
        const statusClass = isDuplicate ? "list-group-item-warning" : "list-group-item-success";
        const statusText = isDuplicate ? i18n.duplicate : i18n.scanned;
        const listItem = $("<li class=\"list-group-item " + statusClass + "\">" +
            "<code>" + trackingCode + "</code> " +
            "<span class=\"badge\">" + statusText + "</span>" +
            "</li>");

        $list.prepend(listItem);  // Add to top of list

        // Update count badge
        $count.text(scannedCodes.length);

        // Fade out status after 2 seconds for duplicates
        if (isDuplicate) {
            setTimeout(function() {
                listItem.fadeOut(500, function() {
                    $(this).remove();
                });
            }, 2000);
        }
    }

    /**
     * Add tracking code to TomSelect field
     * @param {string} trackingCode - The tracking code to add
     */
    function addTrackingCodeToField(trackingCode) {
        const ts = getTS();
        if (!ts) {
            console.error("TomSelect instance not found");
            return false;
        }

        // Check if option already exists
        if (!ts.options[trackingCode]) {
            const option = {
                tracking_code: trackingCode,
                label: trackingCode,
                name: "",
                gkid: ""
            };
            ts.addOption(option);
        }

        // Add item to selection (TomSelect handles duplicates based on maxItems config)
        ts.addItem(trackingCode);

        return true;
    }

    /**
     * Apply all scanned codes to TomSelect field
     */
    function applyScannedCodes() {
        scannedCodes.forEach(function(code) {
            addTrackingCodeToField(code);
        });
    }

    /**
     * Stop QR code scanning and cleanup
     */
    function stopScanning() {
        if (!html5QrCode || !isScanning) {
            return;
        }

        html5QrCode.stop().then(function() {
            isScanning = false;
            $("#qr-reader").empty();
        }).catch(function(err) {
            console.error("QR Scanner stop error:", err);
            isScanning = false;
        });
    }

    /**
     * Finish scanning and apply all codes
     */
    function finishScanning() {
        stopScanning();
        applyScannedCodes();

        // Reset state
        scannedCodes = [];
        $("#qr-codes-list").empty();
        $("#qr-scanned-list").hide();
        $("#qr-scan-count").text("0");

        $(MODAL_SELECTOR).modal("hide");
    }

    /**
     * Show status message
     * @param {string} message - Message to display
     * @param {string} type - Message type ("success" or "info")
     */
    function showStatus(message, type) {
        const $scanner = $(MODAL_SELECTOR);
        const $status = $scanner.find(".qr-status");

        $status
            .removeClass("alert-danger alert-success alert-info")
            .addClass("alert-" + type)
            .html(message)
            .show();
    }

    /**
     * Update UI based on scanning state
     */
    function updateScanningUI() {
        maxItems = getMaxItems();
        const remaining = maxItems - scannedCodes.length;
        const $finishBtn = $("#qr-finish-button");

        if (scannedCodes.length >= maxItems) {
            // Max reached - auto finish
            showStatus("<strong>" + i18n.maxReached + "</strong>", "info");
            setTimeout(function() {
                finishScanning();
            }, 1000);
        } else if (scannedCodes.length > 0) {
            // Show continue message
            const msg = i18n.scanAnother + " (" + remaining + " " + i18n.remaining + ")";
            showStatus(msg, "info");
            $finishBtn.show();
        } else {
            $(MODAL_SELECTOR).find(".qr-status").hide();
            $finishBtn.hide();
        }
    }

    /**
     * Show error message
     * @param {string} message - Error message to display
     */
    function showError(message) {
        const $scanner = $(MODAL_SELECTOR);
        const $status = $scanner.find(".qr-status");

        $status
            .removeClass("alert-success")
            .addClass("alert-danger")
            .html("<strong>" + i18n.error + "</strong> " + message)
            .show();
    }

    /**
     * Handle successful QR code scan
     * @param {string} decodedText - The decoded QR code text
     */
    function onScanSuccess(decodedText) {
        const trackingCode = extractTrackingCode(decodedText);

        if (!trackingCode) {
            showError(i18n.invalidQr);
            return;
        }

        // Check for duplicate
        const isDuplicate = isAlreadyScanned(trackingCode);

        // Add to visual list
        addToScannedList(trackingCode, isDuplicate);

        // Update UI state
        updateScanningUI();
    }

    /**
     * Handle scan failure (silent, happens frequently)
     */
    function onScanFailure() {
        // Silent - scanning errors are normal when no QR code is in view
    }

    /**
     * Start QR code scanning
     */
    function startScanning() {
        if (isScanning) {
            return;
        }

        const $reader = $("#qr-reader");
        $reader.empty(); // Clear previous content

        if (!html5QrCode) {
            // eslint-disable-next-line no-undef
            html5QrCode = new Html5Qrcode("qr-reader");
        }

        html5QrCode.start(
            { facingMode: "environment" }, // Use back camera
            QR_CONFIG,
            onScanSuccess,
            onScanFailure
        ).then(function() {
            isScanning = true;
            $(MODAL_SELECTOR).find(".qr-status").hide();
        }).catch(function(err) {
            console.error("QR Scanner start error:", err);
            showError(i18n.cameraError);
        });
    }

    /**
     * Check if device has camera capability
     * @returns {boolean} - True if device likely has camera support
     */
    function hasCameraSupport() {
        // Check for getUserMedia API support
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }

    /**
     * Detect if user is on mobile device
     * @returns {boolean} - True if mobile or tablet device
     */
    function isMobileDevice() {
        // Check user agent for mobile indicators
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;
        const mobileRegex = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet/i;

        // Also check for touch support (but not only, as some laptops have touch)
        const hasTouch = ("ontouchstart" in window) || (navigator.maxTouchPoints > 0);

        // Check screen size (mobile-like if width < 768px)
        const isSmallScreen = window.innerWidth < 768;

        return mobileRegex.test(userAgent) || (hasTouch && isSmallScreen);
    }

    /**
     * Render QR scanner modal structure inside the shared modal container
     */
    function renderQrScannerModal() {
        const modalMarkup = `
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title" id="modalLabel">
        ${i18n.modalTitle}
        <span class="badge" id="qr-scan-count" style="margin-left: 10px;">0</span>
    </h4>
</div>
<div class="modal-body">
    <div class="alert qr-status" role="alert" style="display: none;"></div>

    <div id="qr-reader" style="width: 100%;"></div>
    <p class="help-block text-center" id="qr-help-text" style="margin-top: 15px;">
        ${i18n.helpText}
    </p>

    <div id="qr-scanned-list" class="panel panel-success" style="display: none; margin-bottom: 15px;">
        <div class="panel-heading">
            <h3 class="panel-title">${i18n.scannedCodesTitle}</h3>
        </div>
        <ul class="list-group" id="qr-codes-list"></ul>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-primary" id="qr-finish-button" style="display: none;">${i18n.finishButton}</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">${i18n.cancelButton}</button>
</div>`;

        $(MODAL_CONTENT_SELECTOR).html(modalMarkup);
    }

    /**
     * Prepare and display the QR scanner modal
     */
    function openQrScannerModal() {
        const $modal = $(MODAL_SELECTOR);

        renderQrScannerModal();

        $modal.off(QR_MODAL_EVENT_NAMESPACE);

        $modal
            .on("shown.bs.modal" + QR_MODAL_EVENT_NAMESPACE, function() {
                scannedCodes = [];
                $("#qr-codes-list").empty();
                $("#qr-scanned-list").hide();
                $("#qr-scan-count").text("0");
                $("#qr-finish-button").hide();
                maxItems = getMaxItems();
                startScanning();
            })
            .on("hidden.bs.modal" + QR_MODAL_EVENT_NAMESPACE, function() {
                stopScanning();
                $modal.off(QR_MODAL_EVENT_NAMESPACE);
            })
            .on("click" + QR_MODAL_EVENT_NAMESPACE, "#qr-finish-button", function(event) {
                event.preventDefault();
                finishScanning();
            });

        $modal.modal("show");
    }

    /**
     * Initialize QR scanner button
     */
    function initQrScannerButton() {
        const $qrButton = $("#nrQrScanButton");

        if (hasCameraSupport() && isMobileDevice()) {
            $qrButton.show();
            $qrButton.on("click", function(event) {
                event.preventDefault();
                openQrScannerModal();
            });
        } else {
            $qrButton.hide();
        }
    }


    /**
     * Initialize the QR scanner module
     */
    function init() {
        // Check if Html5Qrcode is available
        // eslint-disable-next-line no-undef
        if (typeof Html5Qrcode === "undefined") {
            console.error("Html5Qrcode library not loaded");
            return;
        }

        initQrScannerButton();
    }

    // Initialize when DOM is ready
    $(document).ready(init);

})();
//{/literal}
