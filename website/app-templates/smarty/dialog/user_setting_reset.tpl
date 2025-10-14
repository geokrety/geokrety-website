{block name=modal_content}
<div class="modal-header alert-warning">
    <button type="button" class="close" data-dismiss="modal" aria-label="{t}Close{/t}"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Reset Setting to Default{/t}</h4>
</div>

<div class="modal-body">
    <p><strong>{t}Setting{/t}:</strong> <span id="resetSettingName"></span></p>
    <p><strong>{t}Description{/t}:</strong> <span id="resetSettingDescription"></span></p>
    <hr>
    <p><strong>{t}Default Value{/t}:</strong> <span id="resetSettingDefault" class="text-muted"></span></p>
    <p><strong>{t}Current Value{/t}:</strong> <span id="resetSettingCurrent" class="text-primary"></span></p>
    <hr>
    <div class="alert alert-warning" role="alert">
        <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
        {t}Are you sure you want to reset this setting to its default value? This action cannot be undone.{/t}
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Cancel{/t}</button>
    <button type="button" class="btn btn-warning" id="confirmResetSettingBtn">{t}Reset to Default{/t}</button>
</div>
{/block}
