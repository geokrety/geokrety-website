// ----------------------------------- JQUERY - GEOKRET AVATAR UPLOAD - BEGIN

$("div#geokretAvatar").dropzone({
    url: '#',
    method: "POST",
    acceptedFiles: "image/*",
    maxFilesize: {GK_SITE_PICTURE_UPLOAD_MAX_FILESIZE},
    autoProcessQueue: true,
    thumbnailWidth: 100,
    thumbnailHeight: 100,
    dictDefaultMessage: '',
    clickable: "#geokretAvatarUploadButton",
    previewsContainer: "#geokretPicturesList div.panel-body div.gallery",
    hiddenInputContainer: "div#geokretAvatar",

    error: function (file, errorMessage, xhr) {
        alert(parseS3UploadError(errorMessage, xhr));
        this.removeFile(file);
    },
    accept: function (file, done) {
        file.postData = [];
        $.ajax({
            url: "{'geokret_avatar_upload_get_s3_signature'|alias}",
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
        let includes = ['key', 'X-Amz-Credential', 'X-Amz-Algorithm', 'X-Amz-Date', 'Policy', 'X-Amz-Signature'];
        for (let key of includes) {
            if (file.postData.hasOwnProperty(key)) {
                formData.append(key, file.postData[key]);
            }
        }
    },

    init: function () {
        this.on("addedfile", function (files) {
            $("#geokretPicturesList").removeClass("hidden");
        });

        this.on("success", function (file) {
            let dropzone = this;
            $.get("{'picture_html_template'|alias:'key=%KEY%'}".replace('%KEY%', file.s3Key), function (data) {
                dropzone.removeFile(file);
                $("#geokretPicturesList .panel-body > div.gallery").append(data);

                // Refresh img
                refresh();
            });

            function refresh() {
                if ($("#"+file.s3Key+" div span.picture-message").length === 0) {
                    return;
                }
                $.get("{'picture_html_template'|alias:'key=%KEY%'}".replace('%KEY%', file.s3Key), function (data) {
                    $("#"+file.s3Key+" div span.picture-message").closest("div[data-gk-type='picture']").remove();
                    $("#geokretPicturesList .panel-body > div.gallery").append(data);
                    setTimeout(function(){ refresh(file.s3Key) }, {GK_PICTURE_UPLOAD_REFRESH_TIMEOUT});
                }).fail(function() {
                    $("#"+file.s3Key+" div span.picture-message").closest("div[data-gk-type='picture']").remove();
                    alert("{t}Image processing failed. This image type is probably not supported{/t}");
                });
            }
        });

        {include 'js/_dropzone-drop-local.inc.tpl.js'}
    },

});

{include 'js/_dropzone-local.inc.tpl.js'}

// ----------------------------------- JQUERY - GEOKRET AVATAR UPLOAD - END
