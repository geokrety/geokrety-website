{block name=modal_content}
<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Mark this GeoKret as archived?{/t}</h4>
</div>

<form name="markAsArchivedForm" action="{'geokret_mark_as_archived'|alias}" method="post" data-parsley-validate data-parsley-priority-enabled="false" data-parsley-ui-enabled="true">
    <div class="modal-body">

        <p><em>{t}You are about the archive this GeoKret.{/t}</em></p>
        <p>{t}This is useful if you think this GeoKret has been destroyed.{/t}</p>
        <ul>
            <li>{t}It will not appear anymore in your active inventory.{/t}</li>
            <li>{t}It will not appear as present in a cache.{/t}</li>
        </ul>
        <p>{t}If someone discover it later, it will be automatically reactivated.{/t}</p>

        <div class="form-group">
            <label for="comment" class="col-sm-2 control-label">{t}Comment{/t}</label>
            <input type="text" class="form-control" id="comment" name="comment" placeholder="Archiving GeoKret" value="{if isset($smarty.post.comment)}{$smarty.post.comment}{/if}">
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-danger">{t}Archive{/t}</button>
    </div>
</form>
{/block}
