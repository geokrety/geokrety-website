$('#modal').on('hidden.bs.modal', function (event) {
    $(this).find('.modal-content').html('<div class="modal-body"><div class="center-block" style="width: 45px;"><img src="{GK_CDN_IMAGES_URL}/loaders/rings.svg" /></div></div>');
});


function modalLoad(url, callback) {
    $('#modal .modal-content').load(url, function (response, status, xhr) {
        modalLoadErrors(xhr, callback);
    });
}

function modalLoadErrors(xhr, callback) {
    if (xhr.status === 401) {
        showLoginForm();
        return;
    }
    if (xhr.status === 403) {
        // TODO
        return;
    }

    if (callback !== undefined) {
        callback();
    }
}