{include file='macros/picture.tpl'}

<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{$modal_title}</h4>
</div>
<form action="/imgup.php?typ={$type}&id={$id}{if isset($picture_id)}&rename={$picture_id}{/if}" method="post" enctype="multipart/form-data">
    <div class="modal-body">

        <div class="row">
            <div class="col-md-12">
                <input type="hidden" name="goto" value="{$smarty.server.HTTP_REFERER}" />

                {if $editing_mode}
                <input type="hidden" name="formname" value="rename" />
                {else}
                <div cla1ss="form-group">
                    <label for="obrazek" class="col-sm-2">{t}File to upload{/t}</label>
                    <div class="col-sm-10">
                        <input type="file" id="obrazek" name="obrazek" accept="image/*">
                        <input type="hidden" name="MAX_FILE_SIZE" value="{$max_file_size}">
                    </div>
                </div>
                <div class="clearfix"></div>
                {/if}

                <div class="form-group">
                    <label for="opis" class="col-sm-2 control-label">{t}Caption{/t}</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="opis" name="opis" placeholder="Caption" maxlength="50" value="{if isset($picture)}{$picture->legend}{/if}">
                    </div>
                </div>

                {if $type == 1 and not $editing_mode}
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input id="save_desc" name="save_desc" type="checkbox" value="true" {if not empty($save_desc_cookie)} checked{/if})> {t}Remember description{/t} </label> <p class="help-block">
                                {t time=$save_desc_cookie_period}Remember the description for the next %1 minutes{/t}
                                </p>
                        </div>
                    </div>
                </div>
                {/if}

                {if $can_set_avatar and !$editing_mode}
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input id="avatar" name="avatar" type="checkbox"> {t}Use as avatar{/t}
                            </label>
                            <p class="help-block">
                                {t}GeoKret's main picture, displayed under this icon{/t} <img src="{$iconsUrl}/idcard.png" width="14" height="10" alt="idcard" />
                            </p>
                        </div>
                    </div>
                </div>
                {/if}

            </div>
        </div>


        <div class="row">
            <div class="col-xs-6 col-sm-4 col-sm-offset-2">
                <div class="gallery image-preview">
                    {if isset($picture)}
                    {call pictureOrDefault item=$picture}
                    {else}
                    {call pictureOrDefault}
                    {/if}
                </div>
            </div>
            {if !$editing_mode}
            <div class="col-xs-6 col-sm-6">
                <p>
                    <em>
                        <dl>
                            <dt>Supported images type</dt>
                            <dd>jpg, png, gif</dd>
                            <dt>Maximum width</dt>
                            <dd>{$max_width} px</dd>
                            <dt>Maximum height</dt>
                            <dd>{$max_height} px</dd>
                            <dt>Maximum file size</dt>
                            <dd>{$max_file_size}</dd>
                        </dl>
                    </em>
                </p>
            </div>
            {/if}
        </div>


    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-info">{if $editing_mode}{t}Update{/t}{else}{t}Upload{/t}{/if}</button>
    </div>
</form>

<script>
    // ----------------------------------- JQUERY - IMGUP - BEGIN
    $('#opis[maxlength]').maxlength({
        warningClass: "label label-danger",
        limitReachedClass: "label label-success",
    });

    var caption = $('#opis');
    var captionPreview = $('.image-preview figcaption > p:first');

    captionPreview.html(caption.val());
    caption.on('keyup', function() {
        captionPreview.html(caption.val());
    });
    caption.on('change', function() {
        captionPreview.html(caption.val());
    });

    // toggle avatar border color
    $('#avatar').change(function() {
        if (this.checked) {
            $('.image-preview .image-container').addClass('is-avatar');
        } else {
            $('.image-preview .image-container').removeClass('is-avatar');
        }
    });


    // https://stackoverflow.com/a/4459419/944936
    // Load selected picture in preview
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('.image-preview figure img').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#obrazek").change(function() {
        readURL(this);
    });

    // ----------------------------------- JQUERY - IMGUP - END
</script>
