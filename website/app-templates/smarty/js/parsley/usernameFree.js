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
