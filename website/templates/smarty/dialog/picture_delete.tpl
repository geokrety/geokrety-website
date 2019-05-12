{include file='macros/picture.tpl'}

<div class="modal-header alert-danger">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title" id="modalLabel">{t}Do you really want to delete this picture?{/t}</h4>
</div>
<form name="comment" action="{$picture->deleteUrl()}" method="post">
  <div class="modal-body">
    <div class="gallery">
      {call picture item=$picture skipLinkToEntity=true skipTags=true skipButtons=true}
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
    <button type="submit" class="btn btn-danger">{t}Delete{/t}</button>
  </div>
</form>
