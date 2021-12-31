{block name=modal_content}
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Select GeoKrety from inventory{/t}</h4>
</div>
<div class="modal-body">

{if $geokrety}
    <div class="alert alert-info hidden" role="alert" id="maxGKSelctionReached">
        {t max={GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}}Only %1 GeoKrety can be processed at a time.{/t}
    </div>

    <div>
        <form class="form-inline">
            <div class="form-group">
                <label for="gk-filter">{t}Filter{/t}</label>
                <input type="text" class="form-control" id="gk-filter" placeholder="{t}Name or ID{/t}">
            </div>
        </form>
    </div>

    <div class="table-responsive pre-scrollable">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th></th>
                    <th>{t}ID{/t}</th>
                    <th class="text-center">{t}Owner{/t}</th>
                    <th><input type="checkbox" id="geokretySelectAll" title="{t count=GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}Select all (but max %1){/t}" /></th>
                </tr>
            </thead>
            <tbody id="geokretyListTable">
                {foreach from=$geokrety item=geokret}
                <tr>
                    <td>
                        <button class="btn btn-primary" name="btnChooseGK" data-trackingcode="{$geokret->tracking_code}">{t}Choose{/t}</button>
                    </td>
                    <td>
                        {$geokret|gklink:null:'_blank' nofilter} {$geokret|gkavatar nofilter}<br />
                        <small><span class="gk-name" title="{$geokret->name}">{$geokret->gkid}</span></small>
                    </td>
                    <td class="text-center" class="text-center" nowrap>
                        {$geokret->owner|userlink:null:'_blank' nofilter}
                        <br />
                        {if !is_null($geokret->last_move)}
                        {$geokret->last_move|logicon nofilter}
                        {$geokret->last_move->moved_on_datetime|print_date nofilter}
                        {else}
                        {$geokret->created_on_datetime|print_date nofilter}
                        {/if}
                    </td>
                    <td>
                        <input type="checkbox" name="geokretySelected" data-trackingcode="{$geokret->tracking_code}" />
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{else}
    <em>{t}Your inventory is empty.{/t}</em>
{/if}

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
{if $geokrety}
    <button type="submit" class="btn btn-primary" id="modalInventorySelectButton">{t escape=no count=0}Select <span class="badge">%1</span>{/t}</button>
{/if}
</div>
{/block}
