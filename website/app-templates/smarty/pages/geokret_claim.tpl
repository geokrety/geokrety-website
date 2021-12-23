{extends file='base.tpl'}

{block name=title}{t}Claim a GeoKret{/t}{/block}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title" id="modalLabel">{t}Claim a GeoKret{/t}</h4>
    </div>

    <div class="panel-body">
        <form class="form-horizontal" method="post" id="formClaim" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>

            <div class="form-group">
                <label for="inputTrackingCode" class="col-sm-2 control-label">{t}Tracking Code{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputTrackingCode" name="tc" placeholder="ABC123" minlength="6" value="{if isset($smarty.post.tc)}{$smarty.post.tc}{/if}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="inputOwnerCode" class="col-sm-2 control-label">{t}Owner Code{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputOwnerCode" name="oc" placeholder="123456" minlength="6" value="{if isset($smarty.post.oc)}{$smarty.post.oc}{/if}" required>
                </div>
            </div>

            <div class="col-sm-10 col-sm-offset-2">
                <p>
                    <em>{t}The process require a "Tracking Code" generally written on the GeoKret label or engraved on the coin plus an "Owner Code" generally given on a paper sheet.{/t}</em>
                </p>
            </div>

            <div class="modal-footer">
                {call csrf}
                <button type="submit" class="btn btn-primary">{t}Adopt{/t}</button>
            </div>

        </form>
    </div>
</div>
{/block}


{block name=javascript append}
{include 'js/geokrety/geokret_claim.tpl.js'}
{/block}
