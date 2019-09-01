<div class="panel panel-default">
    <div class="panel-heading">
        {t}Found it? Log it!{/t}
    </div>
    <div class="panel-body">
        <form class="form form-horizontal" action="{'move_create'|alias}" method="get">

            <div class="form-group">
                <label for="tracking_code" class="col-sm-2 control-label"><abbr title="Tracking Code">{t}Tracking Code{/t}</abbr></label>
                <div class="col-sm-8">
                    <input class="form-control" type="text" name="tracking_code" id="tracking_code"{if $geokret->isOwner() or $geokret->hasTouchedInThePast()} value="{$geokret->tracking_code}"{/if} size="{GK_SITE_TRACKING_CODE_LENGTH}" maxlength="{GK_SITE_TRACKING_CODE_LENGTH}" placeholder="{t}Please enter the Tracking Code here{/t}">
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-success btn-block">{t}Log it!{/t}</button>
                </div>
            </div>
        </form>
    </div>
</div>
