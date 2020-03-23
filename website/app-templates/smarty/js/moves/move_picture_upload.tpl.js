// ----------------------------------- JQUERY - MOVE PICTURE UPLOAD - BEGIN

$('div.enable-dropzone').each(function() {
    let baseSelector = '#' + $(this).attr('id');
    let moveId = $(this).data('id');

    $(this).dropzone({
        url: '#',
        method: "POST",
        acceptedFiles: "image/*",
        maxFilesize: {GK_SITE_PICTURE_UPLOAD_MAX_FILESIZE},
        autoProcessQueue: true,
        thumbnailWidth: 100,
        thumbnailHeight: 100,
        dictDefaultMessage: '',
        clickable: baseSelector + " button.movePictureUploadButton",
        previewsContainer: baseSelector + " .move-pictures div.gallery",

        accept: function (file, done) {
            file.postData = [];
            $.ajax({
                url: "{'move_picture_upload_get_s3_signature'|alias}".replace('@moveid', moveId),
                data: {
                    filename: file.name
                },
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (!response.success) {
                        done(response.message);
                    }

                    delete response.success;
                    file.custom_status = 'ready';
                    file.postData = response;
                    file.s3Key = response.s3Key;
                    $(file.previewTemplate).addClass('uploading');
                    done();
                },
                error: function (response) {
                    file.custom_status = 'rejected';
                    if (response.responseText) {
                        response = JSON.parse(response.responseText);
                    }
                    if (response.status) {
                        done(response.status);
                        return;
                    }
                    done('error preparing the upload');
                }
            });
        },

        processing: function (file) {
            this.options.url = file.postData.uploadUrl;
        },

        sending: function (file, xhr, formData) {
            for (let key in file.postData) {
                if (file.postData.hasOwnProperty(key)) {
                    formData.append(key, file.postData[key]);
                }
            }
        },

        init: function () {
            this.on("addedfile", function (files) {
                $(baseSelector + " .move-pictures").removeClass("hidden");
            });

            this.on("success", function (file) {
                let dropzone = this;
                console.log('Upload ok');
                $.get("{'picture_html_template'|alias:'key=%KEY%'}".replace('%KEY%', file.s3Key), function (data) {
                    dropzone.removeFile(file);
                    $(baseSelector + " .move-pictures div.row > div.gallery").append(data);
                });
            });

            this.on("error", function (file, errorMessage, xhr) {
                file.previewElement.querySelector("div.dz-error-message span").innerHTML = parseS3UploadError(errorMessage, xhr);
            });
        },

    });
});

// TODO: Move this in a global space
function parseS3UploadError(errorMessage, xhr) {
    let response = $($.parseXML(errorMessage));
    let code = response.find("Code").text();
    if (xhr.status === 400) {
        if (code === 'EntityTooLarge') {
            return "{t}Your upload exceeds the maximum allowed object size.{/t}";
        }
        if (code === 'EntityTooSmall') {
            return "{t}Your upload exceeds the maximum allowed object size.{/t}";
        }
    }
    if (xhr.status === 403) {
        if (code === 'AccessDenied') {
            return "{t}Invalid according to Policy: Policy Condition failed.{/t}";
        }
    }
    console.log('This error message is not catched:', errorMessage);
    return errorMessage;
}

// ----------------------------------- JQUERY - MOVE PICTURE UPLOAD - END
