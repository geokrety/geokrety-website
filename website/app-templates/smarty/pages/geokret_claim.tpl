{extends file='base.tpl'}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Claim a GeoKret{/t}</h3>
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
                    <em>The process require a "Tracking Code" generally written on the GeoKret label or encraved on the coin plus an "Owner Code" generally given on a paper sheet.</em>
                </p>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">{t}Adopt{/t}</button>
                </div>
            </div>

        </form>
    </div>
</div>
{/block}
