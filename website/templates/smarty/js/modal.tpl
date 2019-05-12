
// ----------------------------- MODAL START

$('#modal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget)
  var typeName = button.data('type');
  var modal = $(this);
  modal.find('.modal-content').html('<div class="modal-body"><div class="center-block" style="width: 45px;"><img src="{$imagesUrl}/loaders/rings.svg" /></div></div>');

  if (typeName == 'move-comment') {
    var commentType = button.data('move-comment-type');
    var gkid = button.data('gkid');
    var ruchid = button.data('ruchid');
    modal.find('.modal-content').load('comment.php?gkid='+gkid+'&ruchid='+ruchid+'&type='+commentType);
  } else if (typeName == 'move-delete') {
    var id = button.data('id');
    modal.find('.modal-content').load('_dialog_move_delete.php?id='+id);
  } else if (typeName == 'move-comment-delete') {
    var id = button.data('id');
    modal.find('.modal-content').load('_dialog_move_comment_delete.php?id='+id);
} else if (typeName == 'picture-delete') {
    var id = button.data('id');
    modal.find('.modal-content').load('_dialog_picture_delete.php?id='+id);
  }
})

// ----------------------------- MODAL END
