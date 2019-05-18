<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Adopt a GeoKret{/t}</h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" method="post">

            <div class="form-group">
                <label for="inputTrackingCode" class="col-sm-2 control-label">{t}Tracking Code{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputTrackingCode" name="tc" placeholder="{t}Tracking Code{/t}" value="{if isset($smarty.post.tc)}{$smarty.post.tc}{/if}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputOwnerCode" class="col-sm-2 control-label">{t}Owner Code{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputOwnerCode" name="oc" placeholder="{t}Owner Code{/t}" value="{if isset($smarty.post.oc)}{$smarty.post.oc}{/if}">
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">{t}Adopt{/t}</button>
                </div>
            </div>

        </form>
    </div>
</div>
