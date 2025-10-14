{block name=modal_content}
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="{t}Close{/t}"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Edit Setting{/t}</h4>
</div>

<form id="editUserSettingForm">
    <div class="modal-body">
        <div class="form-group">
            <label id="editSettingName"></label>
            <p class="help-block" id="editSettingDescription"></p>
            <label for="userSettingValue">{t}Current Value{/t}</label>
            <div id="editSettingInputContainer">
                {* Input will be dynamically inserted by JavaScript *}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Cancel{/t}</button>
        <button type="submit" class="btn btn-primary" id="saveUserSettingBtn">{t}Save{/t}</button>
    </div>
</form>
{/block}
