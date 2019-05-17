// ----------------------------- MODAL START

$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');
    var modal = $(this);
    modal.find('.modal-content').html('<div class="modal-body"><div class="center-block" style="width: 45px;"><img src="{$imagesUrl}/loaders/rings.svg" /></div></div>');

    if (typeName == 'move-comment') {
        var commentType = button.data('move-comment-type');
        var gkid = button.data('gkid');
        var ruchid = button.data('ruchid');
        modal.find('.modal-content').load('/comment.php?gkid=' + gkid + '&ruchid=' + ruchid + '&type=' + commentType);
    } else if (typeName == 'move-delete') {
        var id = button.data('id');
        modal.find('.modal-content').load('/_dialog_move_delete.php?id=' + id);
    } else if (typeName == 'move-comment-delete') {
        var id = button.data('id');
        modal.find('.modal-content').load('/_dialog_move_comment_delete.php?id=' + id);
    } else if (typeName == 'picture-upload') {
        var pictureType = button.data('picture-type');
        var id = button.data('id');
        var isAvatar = button.data('is-avatar');
        modal.find('.modal-content').load('/imgup.php?id=' + id + '&typ=' + pictureType + '&pictureid=' + pictureId + (isAvatar ? '&avatar=on' : ''));
    } else if (typeName == 'picture-edit') {
        var pictureType = button.data('picture-type');
        var id = button.data('id');
        var pictureId = button.data('picture-id');
        modal.find('.modal-content').load('/imgup.php?id=' + id + '&typ=' + pictureType + '&rename=' + pictureId);
    } else if (typeName == 'picture-delete') {
        var pictureId = button.data('picture-id');
        modal.find('.modal-content').load('/_dialog_picture_delete.php?id=' + pictureId);
    } else if (typeName == 'picture-set-avatar') {
        var pictureId = button.data('pictureid');
        var geokretId = button.data('geokretid');
        modal.find('.modal-content').load('/_dialog_picture_set_avatar.php?geokretid=' + geokretId + '&pictureid=' + pictureId);
    } else if (typeName == 'user-choose-language') {
        modal.find('.modal-content').load('/_dialog_user_choose_language.php');
    } else if (typeName == 'user-update-email') {
        modal.find('.modal-content').load('/_dialog_user_update_email.php');
    } else if (typeName == 'latlon') {
        modal.find('.modal-content').load('/_dialog_user_update_observation_area.php');
    } else if (typeName == 'secid-refresh') {
        modal.find('.modal-content').load('/api-secid-change.php');
    }
})

// ----------------------------- MODAL END
