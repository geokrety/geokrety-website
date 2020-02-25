$('body').on('change keyup', '#caption', function(event) {
    let caption = $('#caption').val();
    $('div.modal-body .picture-caption').text(caption)
});
