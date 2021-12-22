{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you want to set this picture as main avatar?{/t}</h4>
</div>

<form name="picture" action="{'picture_define_as_main_avatar'|alias:sprintf('key=%s', $picture->key)}" method="POST">
    <div class="modal-body">
        <div class="gallery image-preview">
            {$picture|picture nofilter}
        </div>
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-info">{t}Define{/t}</button>
    </div>
</form>
{/block}
