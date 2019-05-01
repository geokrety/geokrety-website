{include file='macros/picture.tpl'}

<div class="modal-header alert-info">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title" id="modalLabel">{t}Do you really want to set this picture as avatar?{/t}</h4>
</div>
<form action="/geokret_gallery.php?id={$picture->geokretId}" method="post">
  <input type="hidden" name="formname" value="newavatar" />
  <input type="hidden" name="avatarid" value="{$picture->id}" />
  <div class="modal-body">
    <div class="gallery">
      {call picture item=$picture skipLinkToEntity=true skipTags=true skipButtons=true}
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
    <button type="submit" class="btn btn-info">{t}Set{/t}</button>
  </div>
</form>
