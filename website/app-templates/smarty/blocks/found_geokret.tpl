<div class="panel panel-default">
    <div class="panel-heading">
        <h4>{t}Found a GeoKret?{/t}</h4>
    </div>
    <div class="panel-body">
        <p id="found-geokret-label">
            {t escape=no title="Tracking Code"}Please enter the <abbr title="%1">Tracking Code</abbr> here:{/t}
        </p>
        <form class="form" action="{'move_create'|alias}" method="get">

            <div class="form-group">
                <input class="form-control input-lg input-uc tcmaxl" type="text" name="tracking_code" id="tracking_code" size="{GK_SITE_TRACKING_CODE_MAX_LENGTH}" minlength="{GK_SITE_TRACKING_CODE_MIN_LENGTH}" tcminlength="{GK_SITE_TRACKING_CODE_MIN_LENGTH}" maxlength="{GK_SITE_TRACKING_CODE_MAX_LENGTH}" placeholder="{t}Tracking code{/t}">
            </div>

            <button id="found-geokret-submit" type="submit" class="btn btn-success btn-lg btn-block">{t}Log it!{/t}</button>
        </form>
    </div>
</div>
