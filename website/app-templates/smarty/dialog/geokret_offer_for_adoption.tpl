{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Offer this GeoKret for adoption?{/t}</h4>
</div>

<form name="offerForAdoptionForm" action="{'geokret_offer_for_adoption'|alias}" method="post">
    <div class="modal-body">

        <em>{t}This will permit another user who knows the Tracking Code and the Owner Code to become the new owner of this GeoKret.{/t}</em>

    </div>
    <div class="modal-footer">
        <a class="btn btn-default" href="{'geokret_details'|alias}" title="{t}Back to GeoKret page{/t}" data-dismiss="modal">
            {t}Dismiss{/t}
        </a>
        <button type="submit" class="btn btn-info">{t}Generate an Owner Code{/t}</button>
    </div>
</form>
{/block}
