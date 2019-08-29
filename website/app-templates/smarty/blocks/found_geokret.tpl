<div class="panel panel-default">
    <div class="panel-heading">
        <h4>{t}Found a GeoKret?{/t}</h4>
    </div>
    <div class="panel-body">
        <p>
            {t}Please enter the tracking code here:{/t}
        </p>
        <form class="form" action="{'geokrety_move_create'|alias}" method="get">

            <div class="form-group">
                <input class="form-control input-lg" type="text" name="tracking_code" id="tracking_code" size="{GK_SITE_TRACKING_CODE_LENGTH}" maxlength="{GK_SITE_TRACKING_CODE_LENGTH}" placeholder="{t}Tracking code{/t}">
            </div>

            <button type="submit" class="btn btn-success btn-lg btn-block">{t}Log it!{/t}</button>
        </form>
    </div>
</div>
