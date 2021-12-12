
<form class="form-horizontal" id="moveForm" method="POST" action="{if $move->id}{'geokrety_move_edit'|alias}{else}{'move_create'|alias}{/if}" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true data-parsley-excluded="input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled]">
    <input type="hidden" name="app" value="{if $move->app}{$move->app}{else}{GK_APP_NAME}{/if}" />
    <input type="hidden" name="app_ver" value="{if $move->app_ver}{$move->app_ver}{else}{GK_APP_VERSION}{/if}" />
    <div class="hidden" id="accordionParking"></div>

    <div class="panel-group" id="movePanelGroup" role="tablist" aria-multiselectable="true">

        <div id="panelMoveGeoKret" class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingGeokret" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseGeokret" aria-expanded="true" aria-controls="collapseGeokret">
                {t}Identify GeoKret{/t}
                <div class="pull-right" id="geokretHeader"></div>
                <div class="clearfix"></div>
            </div>
            <div id="collapseGeokret" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingGeokret">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="nr" class="col-sm-2 control-label">{t}Tracking Code{/t}</label>
                                <div class="col-sm-10">

                                    <div class="input-group">
                                        <input type="text" name="tracking_code" id="nr" value="{if !is_null($move->geokret)}{$move->geokret->tracking_code}{/if}" minlength="{GK_SITE_TRACKING_CODE_LENGTH}" {if !$f3->get('SESSION.CURRENT_USER')}maxlength="6"{/if} required class="form-control" placeholder="eg. DQ9H4B" aria-describedby="helpBlockTrackingCode" data-parsley-trigger="input focusout" data-parsley-validation-threshold="{GK_SITE_TRACKING_CODE_LENGTH -1}" data-parsley-debounce="500" data-parsley-remote data-parsley-remote-validator="checkNr" data-parsley-errors-messages-disabled style="text-transform:uppercase" data-parsley-group="trackingCode" data-parsley-remote-options='{ "type": "POST" }' />
                                        <span class="input-group-btn">
                                            {if $f3->get('SESSION.CURRENT_USER')}
                                            <button class="btn btn-default" type="button" id="nrInventorySelectButton" title="{t}Select GeoKrety from inventory{/t}" data-toggle="modal" data-target="#modal" data-type="select-from-inventory">{fa icon="briefcase"}</button>
                                            {/if}
                                            <button class="btn btn-default" type="button" id="nrSearchButton" title="{t}Verify Tracking Code{/t}">{fa icon="search"}</button>
                                        </span>
                                    </div>
                                    <p id="helpBlockTrackingCode" class="help-block tooltip_large" data-toggle="tooltip" title="<img src='{GK_CDN_IMAGES_URL}/labels/screenshots/label-screenshot.svg' style='width:100%' />" data-html="true">{t escape=no count={GK_SITE_TRACKING_CODE_LENGTH}}%1 characters from <em>GeoKret label</em>. <u>Do not use the code starting with 'GK' here</u>{/t}</p>
                                </div>
                            </div>
                        </div>
                        <ul class="col-sm-4 alert alert-success hidden list-unstyled" id="nrResult"></ul>
                    </div>

                    <div class="row">
                        <div class="form-group top-buffer">
                            <div class="col-sm-3 col-sm-offset-2">
                                <button type="button" class="btn btn-primary btn-block" id="nrNextButton" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseLogtype" aria-expanded="true" aria-controls="collapseLogtype">{t}Next{/t}</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="panelMoveLogType" class="panel panel-default">
            <div class="panel-heading collapsed" role="tab" id="headingLogtype" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseLogtype" aria-expanded="true" aria-controls="collapseLogtype">
                {t}Log type{/t}
                <div class="pull-right" id="logTypeHeader"></div>
                <div class="clearfix"></div>
            </div>
            <div id="collapseLogtype" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingLogtype">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-lg-4 col-lg-push-8">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <p class="text-center">
                                        {t}Select the action type you've made on the GeoKret. For more information consult the help page.{/t}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 col-lg-pull-4 top-buffer">
                            <div class="form-group" id="infoLogtypeFormGroup">
                                <div class="col-sm-10 col-sm-offset-1">
                                    <label>
                                        <input type="radio" name="logtype" id="logType{\GeoKrety\LogType::LOG_TYPE_DROPPED}" value="{\GeoKrety\LogType::LOG_TYPE_DROPPED}" {if $move->move_type->isType(\GeoKrety\LogType::LOG_TYPE_DROPPED)}checked{/if} required data-parsley-group="logtype">
                                        <div class="dropped box" data-toggle="tooltip" title="{t}When you've left a GeoKret in a cache{/t}">
                                            <span>{t}I've dropped GeoKret{/t}</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType{\GeoKrety\LogType::LOG_TYPE_GRABBED}" value="{\GeoKrety\LogType::LOG_TYPE_GRABBED}" {if $move->move_type->isType(\GeoKrety\LogType::LOG_TYPE_GRABBED)}checked{/if} required>
                                        <div class="grabbed box" data-toggle="tooltip" title="{t}When you've taken a GeoKret from a cache and are not going to put it to another cache <i>soon</i>{/t}" data-html="true">
                                            <span>{t}I've grabbed GeoKret{/t}</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType{\GeoKrety\LogType::LOG_TYPE_SEEN}" value="{\GeoKrety\LogType::LOG_TYPE_SEEN}" {if $move->move_type->isType(\GeoKrety\LogType::LOG_TYPE_SEEN)}checked{/if} required>
                                        <div class="met box" data-toggle="tooltip" title="{t}When you've met a GeoKret in a cache but haven't taken it with you{/t}">
                                            <span>{t}I've met GeoKret{/t}</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType{\GeoKrety\LogType::LOG_TYPE_DIPPED}" value="{\GeoKrety\LogType::LOG_TYPE_DIPPED}" {if $move->move_type->isType(\GeoKrety\LogType::LOG_TYPE_DIPPED)}checked{/if} required>
                                        <div class="dipped box" data-toggle="tooltip" title="{t}When you take a GeoKret for a cache-tour; this is the same as doing a drop and then grab - the visited location is logged but GeoKret is still in your inventory{/t}">
                                            <span>{t}I've dipped a GeoKret{/t}</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType{\GeoKrety\LogType::LOG_TYPE_COMMENT}" value="{\GeoKrety\LogType::LOG_TYPE_COMMENT}" {if $move->move_type->isType(\GeoKrety\LogType::LOG_TYPE_COMMENT)}checked{/if} required>
                                        <div class="comment box" data-toggle="tooltip" title="{t}When you want to write a comment :){/t}">
                                            <span>{t}Comment{/t}</span>
                                        </div>
                                    </label>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-3 col-sm-offset-2">
                                <button type="button" class="btn btn-primary btn-block" id="logtypeNextButton">{t}Next{/t}</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="panelLocation" class="panel panel-default">
            <div class="panel-heading collapsed" role="tab" id="headingLocation" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseLocation" aria-expanded="true" aria-controls="collapseLocation">
                {t}New location{/t}
                <div class="pull-right" id="locationHeader"></div>
                <div class="clearfix"></div>
            </div>
            <div id="collapseLocation" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingLocation">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">{t}Waypoint{/t}</label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <input type="text" name="waypoint" id="wpt" value="{$move->waypoint}" minlength="{GK_CHECK_WAYPOINT_MIN_LENGTH}" maxlength="{GK_CHECK_WAYPOINT_MAX_LENGTH}" data-parsley-validate-if-empty class="form-control" aria-describedby="helpBlockWaypoint" placeholder="{t}eg. GC1AQ2N{/t}" data-parsley-trigger="input focusout" data-parsley-validation-threshold="5" data-parsley-remote data-parsley-remote-validator="checkWpt" data-parsley-group="location" data-parsley-errors-messages-disabled data-parsley-debounce="500" data-parsley-remote-options='{ "type": "POST" }' style="text-transform:uppercase">
                                        <span class="input-group-btn">
{if isset($current_user) && $current_user->hasHomeCoordinates()}
                                            <button class="btn btn-default" type="button" id="wptHomeButton">{fa icon="home"}</button>
{/if}
                                            <button class="btn btn-default" type="button" id="wptSearchByNameButton" title="{t}Lookup opencaching cache by name{/t}"><img src="{GK_CDN_IMAGES_URL}/logos/geocaching.svg" width="18px" /></button>
                                            <button class="btn btn-default" type="button" id="wptSearchButton">{fa icon="search"}</button>
                                        </span>
                                    </div>
                                    <p id="helpBlockWaypoint" class="help-block">
                                        {t}eg.: GC1AQ2N, OP069B, OC033A…{/t}
                                        <a href="{'help'|alias:null:null:'#fullysupportedwaypoints'}" target="_blank">
                                            {fa icon="question-circle"}
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group hidden" id="findbyCacheName">
                                <label class="col-sm-2 control-label">Cache name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="findbyCacheNameInput" id="findbyCacheNameInput" size="20" class="form-control" aria-describedby="helpBlockCacheName">
                                    <p id="helpBlockCacheName" class="help-block">
                                        {t escape=no}Enter cache name. <strong>Does not work for GC caches</strong>{/t}.
                                        <a href="{'help'|alias:null:null:'#fullysupportedwaypoints'}" target="_blank">
                                            {fa icon="question-circle"}
                                        </a>
                                    </p>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-6">


                            <div class="panel panel-default map-togglable" id="mapField">
                                <div class="panel-heading">
                                    {t}Coordinates{/t} {fa icon="pencil"}
                                    <div class="pull-right" id="cacheName"></div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group coordinates-togglable" id="coordinateField">
                                        <div class="col-sm-12">
                                            <div class="input-group">
                                                <input type="text" id="latlon" name="coordinates" value="{$move->coordinates}" class="form-control" aria-describedby="helpBlockCoordinates" required data-parsley-group="location" data-parsley-trigger="focusout" data-parsley-debounce="500" data-parsley-remote data-parsley-remote-validator="checkCoordinates" data-parsley-remote-options='{ "type": "POST" }' data-parsley-errors-messages-disabled>
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button" title="Validate coordinates" id="coordinatesSearchButton">{fa icon="search"}</button>
                                                    <!--button class="btn btn-default" type="button" title="Log at my home position" id="homeLocationButton">{fa icon="home"}</button-->
                                                    <!--button class="btn btn-default" type="button" title="Use geolocation" id="geolocationButton">{fa icon="location-arrow"}</button-->
                                                    <a href="{'help'|alias:null:null:'#acceptableformats'}" target="_blank" class="btn btn-default" type="button" title="Acceptable geographic coordinates formats" id="geolocationButton">{fa icon="question-circle"}</a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="mapid" class="leaflet-container-200"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group top-buffer">
                            <div class="col-sm-3 col-sm-offset-2">
                                <button type="button" class="btn btn-primary btn-block" id="locationNextButton" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseMessage" aria-expanded="true" aria-controls="collapseMessage">{t}Next{/t}</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="panelMoveAdditionalData" class="panel panel-default" id="additionalDataPanel">
            <div class="panel-heading collapsed" role="tab" id="headingMessage" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseMessage" aria-expanded="true" aria-controls="collapseMessage">
                {t}Additional data{/t}
                <div class="pull-right" id="additionalDataHeader"></div>
                <div class="clearfix"></div>
            </div>
            <div id="collapseMessage" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingMessage">
                <div class="panel-body">

                    <div class="form-group">
                        <label for="inputDate" class="col-sm-2 control-label">{t}Date{/t}</label>
                        <div class="col-sm-6">
                            <div class="input-group date" id="datetimepicker">
                                <input type="text" class="form-control" name="date" id="inputDate" readonly required data-parsley-group="additionalData" data-parsley-datebeforenow="llll" data-parsley-dateaftergkbirth="llll" data-parsley-trigger="focusout" data-parsley-trigger-after-failure="focusout" />
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="date" id="inputHiddenDate" required />
                    <input type="hidden" name="hour" id="inputHiddenHour" required />
                    <input type="hidden" name="minute" id="inputHiddenMinute" required />
                    <input type="hidden" name="tz" id="inputHiddenTimezone" required />

                    {if !$f3->get('SESSION.CURRENT_USER')}
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{t}Username{/t}</label>
                        <div class="col-sm-6">
                            <input type="text" name="username" id="username" value="{$move->username}" data-toggle="tooltip" data-html="true" class="form-control" title="{t}This may be your:<br />- geocaching/opencaching username<br />- nickname<br />- name, etc.{/t}" minlength="{GK_USERNAME_MIN_LENGTH}" maxlength="{GK_USERNAME_MAX_LENGTH}" required data-parsley-group="additionalData" data-parsley-trigger="input focusout" />
                        </div>
                    </div>
                    {/if}

                    <div class="form-group">
                        <label class="col-sm-2 control-label">{t}Comment{/t}</label>
                        <div class="col-sm-10">
                            <textarea id="comment" name="comment" rows="12" maxlength="5120" class="form-control" aria-describedby="helpBlockComment" data-parsley-group="additionalData" data-parsley-trigger="input focusout">{$move->comment}</textarea>
                            <p id="helpBlockComment" class="help-block">
                                {t}It is always nice to receive a little message ;){/t}
                            </p>
                        </div>
                    </div>

                    {call recaptcha}

{if !$f3->get('SESSION.CURRENT_USER')}
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-2">
                            {include file='banners/geokret_anonymous_log.tpl'}
                        </div>
                    </div>
{/if}

                    <div class="form-group">
                        <div class="col-sm-3 col-sm-offset-2">
                            <button type="button" id="submitButton" class="btn btn-primary btn-block">{t}Post your log{/t}</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

</form>
