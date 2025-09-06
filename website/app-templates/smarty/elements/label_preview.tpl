
{if isset($geokret)}
    <div class="alert alert-info alert-dismissible" role="alert">
        {t url={'move_create_short'|alias:sprintf('@tracking_code=%s', $geokret->tracking_code)}}Short tracking url: %1{/t}
    </div>
{/if}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}GeoKret label preview{/t}</h3>
    </div>
    <div class="panel-body">
        <a id="geokretLabelPreviewLink" href="" class="picture-link" title="{t}GeoKret label preview{/t}">
            <img id="geokretLabelPreview" class="img-responsive center-block" alt="{t}GeoKret label preview{/t}">
        </a>
    </div>
</div>
