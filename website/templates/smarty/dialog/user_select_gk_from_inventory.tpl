<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title" id="modalLabel">{t}Select GeoKrety{/t}</h4>
</div>
<form name="GKSelector">
  <div class="modal-body">

        {include file="blocks/geokrety_table_select.tpl"}

  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
    <button type="submit" class="btn btn-primary">{t}Select{/t}</button>
  </div>
</form>
