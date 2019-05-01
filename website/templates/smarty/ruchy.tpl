<ol class="breadcrumb">
    <li><a href="/">{t}Home{/t}</a></li>
    <li class="active">{t}Log a GeoKret{/t}</li>
</ol>

<form class="form-horizontal" id="moveForm" method="POST" {{if $tripStep->ruchId}}action="?ruchid={{$tripStep->ruchId}}"{{/if}} data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true data-parsley-excluded="input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled]">
    <input type="hidden" name="app" value="www" required />
    <input type="hidden" name="app_ver" value="{CONFIG_GK_VERSION}" required />
    <div class="hidden" id="accordionParking"></div>

    <div class="panel-group" id="movePanelGroup" role="tablist" aria-multiselectable="true">

        <div class="panel panel-default">
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
                                        <input type="text" name="nr" id="nr" value="{{$tripStep->geokretNr}}" minlength="6" {if !$isLoggedIn}maxlength="6"{/if} required class="form-control" placeholder="eg. DQ9H4B" aria-describedby="helpBlockTrackingCode" data-parsley-trigger="input focusout"
                                            data-parsley-validation-threshold="5" data-parsley-remote data-parsley-remote-validator="checkNr" data-parsley-errors-messages-disabled style="text-transform:uppercase" data-parsley-group="trackingCode" />
                                        <span class="input-group-btn">
                                            {if $isLoggedIn}
                                            <button class="btn btn-default" type="button" id="nrInventorySelectButton" title="{t}Select GeoKrety from inventory{/t}" data-toggle="modal" data-target="#modal" data-type="select-from-inventory">{fa
                                                icon="briefcase"}</button>
                                            {/if}
                                            <button class="btn btn-default" type="button" id="nrSearchButton" title="{t}Verify tracking code{/t}">{fa icon="search"}</button>
                                        </span>
                                    </div>
                                    <span id="helpBlockTrackingCode" class="help-block tooltip_large" data-toggle="tooltip" title="<img src='{$imagesUrl}/labels/screenshots/label-screenshot.svg' style='width:100%' />" data-html="true">{t escape=no}6 characters
                                        from <em>GeoKret label</em>. <u>Do not use the code starting with 'GK' here</u>{/t}</span>
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

        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingLogtype" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseLogtype" aria-expanded="true" aria-controls="collapseLogtype">
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
                                        <input type="radio" name="logtype" id="logType{{\Geokrety\Domain\LOG_TYPE_DROPPED}}" value="{{\Geokrety\Domain\LOG_TYPE_DROPPED}}" {{if $tripStep->isType(\Geokrety\Domain\LOG_TYPE_DROPPED)}}checked{{/if}} required data-parsley-group="logtype">
                                        <div class="dropped box" data-toggle="tooltip" title="{t}When you've left a GeoKret in a cache{/t}">
                                            <span>{t}I've dropped GeoKret{/t}</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType{{\Geokrety\Domain\LOG_TYPE_GRABBED}}" value="{{\Geokrety\Domain\LOG_TYPE_GRABBED}}" {{if $tripStep->isType(\Geokrety\Domain\LOG_TYPE_GRABBED)}}checked{{/if}} required>
                                        <div class="grabbed box" data-toggle="tooltip" title="{t}When you've taken a GeoKret from a cache and are not going to put it to another cache <i>soon</i>{/t}" data-html="true">
                                            <span>{t}I've grabbed GeoKret{/t}</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType{{\Geokrety\Domain\LOG_TYPE_SEEN}}" value="{{\Geokrety\Domain\LOG_TYPE_SEEN}}" {{if $tripStep->isType(\Geokrety\Domain\LOG_TYPE_SEEN)}}checked{{/if}} required>
                                        <div class="met box" data-toggle="tooltip" title="{t}When you've met a GeoKret in a cache but haven't taken it with you{/t}">
                                            <span>{t}I've met GeoKret{/t}</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType{{\Geokrety\Domain\LOG_TYPE_DIPPED}}" value="{{\Geokrety\Domain\LOG_TYPE_DIPPED}}" {{if $tripStep->isType(\Geokrety\Domain\LOG_TYPE_DIPPED)}}checked{{/if}} required>
                                        <div class="dipped box" data-toggle="tooltip" title="{t}When you take a GeoKret for a cache-tour; this is the same as doing a drop and then grab - the visited location is logged but GeoKret is still in your inventory{/t}">
                                            <span>{t}I've dipped a GeoKret{/t}</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType{{\Geokrety\Domain\LOG_TYPE_COMMENT}}" value="{{\Geokrety\Domain\LOG_TYPE_COMMENT}}" {{if $tripStep->isType(\Geokrety\Domain\LOG_TYPE_COMMENT)}}checked{{/if}} required>
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

        <div class="panel panel-default" id="panelLocation">
            <div class="panel-heading" role="tab" id="headingLocation" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseLocation" aria-expanded="true" aria-controls="collapseLocation">
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
                                        <input type="text" name="wpt" id="wpt" value="{{$tripStep->waypoint}}" minlength="4" maxlength="20" required class="form-control" aria-describedby="helpBlockWaypoint" placeholder="eg. GC1AQ2N" data-parsley-trigger="input focusout"
                                            data-parsley-validation-threshold="5" data-parsley-remote data-parsley-remote-validator="checkWpt" data-parsley-group="location" data-parsley-errors-messages-disabled data-parsley-debounce="500" data-parsley-pattern="^[a-zA-Z0-9]+" style="text-transform:uppercase">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" id="wptSearchByNameButton" title="{t}Lookup opencaching cache by name{/t}"><img src="{$imagesUrl}/logos/geocaching.svg" width="18px" /></button>
                                            <button class="btn btn-default" type="button" id="wptSearchButton">{fa icon="search"}</button>
                                        </span>
                                    </div>
                                    <span id="helpBlockWaypoint" class="help-block">
                                        {t}eg.: GC1AQ2N, OP069B, OC033A…{/t}
                                        <a href="help.php#fullysupportedwaypoints">
                                            {fa icon="question-circle"}
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group hidden" id="findbyCacheName">
                                <label class="col-sm-2 control-label">Cache name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="findbyCacheNameInput" id="findbyCacheNameInput" size="20" class="form-control" aria-describedby="helpBlockCacheName">
                                    <span id="helpBlockCacheName" class="help-block">
                                        {t escape=no}Enter cache name. <strong>Does not work for GC caches</strong>{/t}.
                                        <a href="help.php#fullysupportedwaypoints">
                                            {fa icon="question-circle"}
                                        </a>
                                    </span>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-6">


                            <div class="panel panel-default map-togglable" id="mapField">
                                <div class="panel-heading">
                                    {t}Coordinates{/t}
                                    <div class="pull-right" id="cacheName"></div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group coordinates-togglable" id="coordinateField">
                                        <div class="col-sm-12">
                                            <div class="input-group">
                                                <input type="text" id="latlon" name="latlon" value="{{' '|implode:$tripStep->getCoordinates()}}" class="form-control" aria-describedby="helpBlockCoordinates" required data-parsley-group="location" data-parsley-trigger="focusout" data-parsley-trigger-after-failure="focusout" data-parsley-remote data-parsley-remote-validator="checkCoordinates"
                                                    data-parsley-errors-messages-disabled>
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button" title="Validate coordinates" id="coordinatesSearchButton">{fa icon="search"}</button>
                                                    <!--button class="btn btn-default" type="button" title="Log at my home position" id="homeLocationButton">{fa icon="home"}</button-->
                                                    <!--button class="btn btn-default" type="button" title="Use geolocation" id="geolocationButton">{fa icon="location-arrow"}</button-->
                                                    <a href="/help.php#acceptableformats" target="_blank" class="btn btn-default" type="button" title="Acceptable geographic coordinates formats" id="geolocationButton">{fa icon="question-circle"}</a>
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

        <div class="panel panel-default" id="additionalDataPanel">
            <div class="panel-heading" role="tab" id="headingMessage" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseMessage" aria-expanded="true" aria-controls="collapseMessage">
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
                                <input type="text" class="form-control" name="inputDate" id="inputDate" readonly required data-parsley-group="additionalData" data-parsley-datebeforenow="llll" data-parsley-dateaftergkbirth="llll" data-parsley-trigger="focusout" data-parsley-trigger-after-failure="focusout" />
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="data" id="inputHiddenDate" required />
                    <input type="hidden" name="godzina" id="inputHiddenHour" required />
                    <input type="hidden" name="minuta" id="inputHiddenMinute" required />

                    {if !$isLoggedIn}
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{t}Username{/t}</label>
                        <div class="col-sm-6">
                            <input type="text" name="username" id="username" value="{{$tripStep->username}}" data-toggle="tooltip" data-html="true" class="form-control" title="{t}This may be your:<br />- geocaching/opencaching username<br />- nickname<br />- name, etc.{/t}" minlength="3"
                                maxlength="20" required data-parsley-group="additionalData" data-parsley-trigger="input focusout" />
                        </div>
                    </div>
                    {/if}

                    <div class="form-group">
                        <label class="col-sm-2 control-label">{t}Comment{/t}</label>
                        <div class="col-sm-6">
                            <textarea id="comment" name="comment" rows="6" maxlength="5120" class="form-control" aria-describedby="helpBlockComment" data-parsley-group="additionalData" data-parsley-trigger="input focusout">{{$tripStep->comment}}</textarea>
                            <span id="helpBlockComment" class="help-block">
                                {t}It is always nice to receive a little message ;){/t}
                            </span>
                        </div>
                    </div>

{if isset($GOOGLE_RECAPTCHA_PUBLIC_KEY) && !$smarty.session.isLoggedIn}
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-2">
                            <div class="g-recaptcha" data-sitekey="{$GOOGLE_RECAPTCHA_PUBLIC_KEY}" id="recaptcha_wrapper"></div>
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
