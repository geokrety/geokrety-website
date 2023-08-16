
{block name=modal_content}
<div class="modal-header alert-{if isset($variant)}{$variant}{else}info{/if}">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{$title}</h4>
</div>

<div class="modal-body">
    {$message}
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Acknowledge{/t}</button>
</div>
{/block}
