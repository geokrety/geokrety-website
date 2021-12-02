// Initialize ZXCVBN
$('#passwordInput').strengthify({
    zxcvbn: '{GK_CDN_ZXCVBN_JS}'
});

// Events for DATE
$('#usernameInput').parsley().on('field:ajaxoptions', function(el, options) {
    options['data']['email'] = $("#emailInput").val();
});

{include 'js/parsley/usernameFree.js'}
