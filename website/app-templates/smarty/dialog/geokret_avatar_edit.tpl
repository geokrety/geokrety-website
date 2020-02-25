{include file='macros/picture_geokret_avatar.tpl'}

{block name=modal_content}
    <div class="modal-header alert-info">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="modalLabel">{t}Manage GeoKret avatar picture{/t}</h4>
    </div>
    <form action="{'geokret_avatar_edit'|alias:sprintf('key=%s', $picture->key)}" method="post" class="form-horizontal">
        <div class="modal-body">

            <div class="form-group">
                <label for="opis" class="col-sm-2 control-label">{t}Caption{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="caption" name="caption" placeholder="Caption" maxlength="{GK_PICTURE_CAPTION_MAX_LENGTH}" value="{$picture->caption}">
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label>
                            <input id="save_geokret_avatar_caption" name="save_geokret_avatar_caption" type="checkbox" value="true"{if $save_geokret_avatar_caption} checked{/if}>
                            {t}Remember caption{/t}
                        </label>
                        <p class="help-block">
                            {t time=GK_SITE_CACHE_TTL_PICTURE_CAPTION/60}Remember the caption for the next %1 minutes{/t}
                        </p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-xs-6 col-sm-4 col-sm-offset-2">
                    <div class="gallery image-preview">
                        {if isset($picture)}
                            {call geokret_avatar_default item=$picture}
                        {else}
                            {call geokret_avatar_default}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{t}Skip{/t}</button>
            <button type="submit" class="btn btn-info">{t}Save{/t}</button>
        </div>
    </form>
{/block}

{block name=javascript_modal append}
{include 'js/dialogs/dialog_geokret_avatar_upload.tpl.js'}
{/block}
