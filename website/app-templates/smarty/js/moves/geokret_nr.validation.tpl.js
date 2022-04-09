

// Validate Tracking code
window.Parsley.addAsyncValidator('checkNr', function(xhr) {
    let valid = 200 === xhr.status;
    let data = $.parseJSON(xhr.responseText);
    this.removeError('errorNr');
    if (valid) {
        data = $.parseJSON(xhr.responseText);
        // Display fetched GK infos
        var result = '';
        data.forEach(geokret => {
            result = result + '<li>' + geokret.html + '</li>';
        });
        $("#nrResult").html(result).show().removeClass("hidden");
    } else {
        data.forEach(error => {
            this.addError('errorNr', { message: error });
        });
        $("#nrResult").hide();
    }
    return valid;
}, '{'validate_tracking_code'|alias}')
