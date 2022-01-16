<div class="panel panel-default">
    <div class="panel-body">
        <form class="form-horizontal" method="post" action="{'search_by_geokret_post'|alias}" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
            <div class="form-group">
                <label for="inputGeokret" class="col-sm-2 control-label">{t}GeoKret{/t}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control maxl" id="inputGeokret" name="geokret" placeholder="{t}GeoKret name or id{/t}" minlength="{GK_GEOKRET_NAME_MIN_LENGTH}" maxlength="{GK_GEOKRET_NAME_MAX_LENGTH}" required>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-block" id="buttonGeokretSubmit" name="buttonGeokretSubmit">{t}Find{/t}</button>
                </div>
            </div>
        </form>

        <form class="form-horizontal" method="post" action="{'search_by_user_post'|alias}" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
            <div class="form-group">
                <label for="inputUsername" class="col-sm-2 control-label">{t}Username{/t}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="inputUsername" name="username" placeholder="{t}Username{/t}" required>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-block" id="buttonUsernameSubmit" name="buttonUsernameSubmit">{t}Find{/t}</button>
                </div>
            </div>
        </form>

        <form class="form-horizontal" method="post" action="{'search_by_waypoint_post'|alias}" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
            <div class="form-group">
                <label for="inputWaypoint" class="col-sm-2 control-label">{t}Waypoint{/t}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="inputWaypoint" name="waypoint" placeholder="{t}Waypoint{/t}" required>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-block" id="buttonWaypointSubmit" name="buttonWaypointSubmit">{t}Find{/t}</button>
                </div>
            </div>
        </form>

    </div>
</div>
