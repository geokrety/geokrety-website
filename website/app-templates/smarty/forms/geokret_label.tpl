<div class="panel panel-default">
    <div class="panel-heading">
         <h3 class="panel-title">{t}Generate a GeoKret Label{/t}</h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
            <div class="form-group">
                <label for="inputGeokretName" class="col-sm-2 control-label">{t}GeoKret name{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control maxl" id="inputGeokretName" name="name" placeholder="{t}GeoKret name{/t}" minlength="{GK_GEOKRET_NAME_MIN_LENGTH}" maxlength="{GK_GEOKRET_NAME_MAX_LENGTH}" required value="{if isset($geokret)}{$geokret->name}{/if}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="inputGeokretOwnerName" class="col-sm-2 control-label">{t}Owner{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputGeokretOwnerName" name="ownerName" placeholder="{t}Owner{/t}" required value="{if isset($geokret)}{$geokret->owner->username}{/if}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="inputGeokretReferenceNumber" class="col-sm-2 control-label">{t}Reference numbers{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputGeokretReferenceNumber" name="referenceNumber" placeholder="{t}Reference number{/t}" required value="{if isset($geokret)}{$geokret->gkid}{/if}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="inputGeokretTrackingCode" class="col-sm-2 control-label">{t}Tracking Code{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputGeokretTrackingCode" name="trackingCode" placeholder="{t}Tracking Code{/t}" required value="{if isset($geokret)}{$geokret->tracking_code}{/if}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="inputMission" class="col-sm-2 control-label">{t}Mission{/t}</label>
                <div class="col-sm-10">
                    <textarea class="form-control maxl" rows="5" id="inputMission" name="mission" placeholder="{t}What is this GeoKret mission?{/t}" maxlength="5120">{if isset($geokret)}{$geokret->mission}{/if}</textarea>
                </div>
            </div>

            {include file='elements/label_form_common.tpl'}

        </form>
    </div>
</div>

{include file='elements/label_preview.tpl'}
