<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Select GeoKrety{/t}</h4>
</div>
<div class="modal-body">

    <div class="alert alert-info hidden" role="alert" id="maxGKSelctionReached">
        {t max={CHECK_NR_MAX_PROCESSED_ITEMS}}Only %1 GeoKrety can be processed at a time.{/t}
    </div>
    {include file="blocks/geokrety_table_select.tpl"}

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
    <button type="submit" class="btn btn-primary" id="modalInventorySelectButton">{t escape=no count=0}Select <span class="badge">%1</span>{/t}</button>
</div>
