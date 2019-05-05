<div class="modal-header alert-danger">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title" id="modalLabel">{t}Do you really want to delete this move comment?{/t}</h4>
</div>
<form name="comment" action="comment.php?delete={$comment_id}&confirmed=1" method="post">
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
    <button type="submit" class="btn btn-danger">{t}Delete{/t}</button>
  </div>
</form>
