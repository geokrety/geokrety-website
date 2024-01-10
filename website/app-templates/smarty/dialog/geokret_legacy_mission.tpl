{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Here is the old mission content from GKv1{/t}</h4>
</div>

<div class="modal-body">
    <pre>{$geokret->legacy_mission}</pre>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
</div>
{/block}
