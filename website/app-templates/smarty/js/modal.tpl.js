$('#modal').on('hidden.bs.modal', function(event) {
    $(this).find('.modal-content').html('<div class="modal-body"><div class="center-block" style="width: 45px;"><img src="{GK_CDN_IMAGES_URL}/loaders/rings.svg" /></div></div>');
})
