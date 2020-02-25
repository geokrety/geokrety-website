// Initialize ZXCVBN
$('#passwordInput').strengthify({
    zxcvbn: '{GK_CDN_ZXCVBN_JS}'
});

// Events for DATE
$('#usernameInput').parsley().on('field:ajaxoptions', function(el, options) {
    options['data']['email'] = $("#emailInput").val();
});

// Define error message
window.Parsley.addAsyncValidator('usernameFreeValidator', function(xhr) {
    var valid = 200 === xhr.status;
    this.removeError('errorUsername');
    this.removeError('remote');

    if (valid) {
        return valid;
    }

    if (!valid) {
        var data = $.parseJSON(xhr.responseText);
        this.addError('errorUsername', {
            message: data
        })
    }
    return valid;

}, "{'validate_username_free'|alias}",

);
