// GK namespace
window.GK = window.GK || {};

//{literal}
/**
 * Display a dynamic flash message
 * @param {string} text - Message text (can contain HTML)
 * @param {string} status - Bootstrap alert type: success, info, warning, danger
 * @param {number} autoClose - Auto-close after milliseconds (0 = no auto-close)
 */
GK.flashMessage = function(text, status, autoClose) {
    status = status || "info";
    autoClose = autoClose || 0;

    const alertHtml = `
        <div class="alert alert-${status} alert-dismissible flash-message" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            ${text}
        </div>
    `;

    const $alert = $(alertHtml);
    $("div.container").first().prepend($alert);

    // Scroll to top to show the message
    $("html, body").animate({ scrollTop: 0 }, 300);

    // Auto-close if specified
    if (autoClose > 0) {
        setTimeout(function() {
            $alert.fadeOut(300, function() {
                $(this).remove();
            });
        }, autoClose);
    }
};
//{/literal}
