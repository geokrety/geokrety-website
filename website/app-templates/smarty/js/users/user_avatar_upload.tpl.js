// ----------------------------------- JQUERY - USER AVATAR UPLOAD - BEGIN

$("div#userAvatar").dropzone({
    url: '#',
    method: "POST",
    acceptedFiles: "image/*",
    maxFilesize: {GK_SITE_PICTURE_UPLOAD_MAX_FILESIZE},
    autoProcessQueue: true,
    thumbnailWidth: 100,
    thumbnailHeight: 100,
    dictDefaultMessage: '',
    clickable: "#userAvatarUploadButton",
    previewsContainer: "#userPicturesList div.panel-body > div.gallery",
    hiddenInputContainer: "div#userAvatar",

    error: function (file, message) {
        alert(message);
        this.removeFile(file);
    },
    accept: function (file, done) {
        file.postData = [];
        $.ajax({
            url: "{'user_avatar_upload_get_s3_signature'|alias}",
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
            $("#userPicturesList").removeClass("hidden");
        });

        this.on("success", function (file) {
            let dropzone = this;
            $.get("{'picture_html_template'|alias:'key=%KEY%'}".replace('%KEY%', file.s3Key), function (data) {
                dropzone.removeFile(file);
                $("#userPicturesList .panel-body > div.gallery").append(data);

                // Refresh img
                refresh(file.s3Key);
            });

            function refresh(fileKey) {
                if ($("#"+fileKey+" div span.picture-message").length === 0) {
                    return;
                }
                $.get("{'picture_html_template'|alias:'key=%KEY%'}".replace('%KEY%', file.s3Key), function (data) {
                    $("#"+file.s3Key+" div span.picture-message").closest("div.gallery").remove();
                    $("#userPicturesList .panel-body > div.gallery").append(data);
                    setTimeout(function(){ refresh(fileKey) }, {GK_PICTURE_UPLOAD_REFRESH_TIMEOUT});
                }).fail(function() {
                    $("#"+file.s3Key+" div span.picture-message").closest("div.gallery").remove();
                    alert("{t}Image processing failed. This image type is probably not supported{/t}");
                });
            }
        });

        this.on("error", function (file, errorMessage, xhr) {
            file.previewElement.querySelector("div.dz-error-message span").innerHTML = parseS3UploadError(errorMessage, xhr);
        });

        {include 'js/_dropzone-drop-local.inc.tpl.js'}
    },
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

// ----------------------------------- JQUERY - USER AVATAR UPLOAD - END
