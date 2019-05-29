<ol class="breadcrumb">
    <li><a href="/">{t}Home{/t}</a></li>
    <li class="active">{t}Log a GeoKret{/t}</li>
</ol>

<form class="form-horizontal" id="moveForm" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
    <div class="hidden" id="accordionParking"></div>

    <div class="panel-group" id="movePanelGroup" role="tablist" aria-multiselectable="true">

        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingLogtype" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseLogtype" aria-expanded="true" aria-controls="collapseLogtype">
                Log type
                <div class="pull-right" id="logTypeHeader"></div>
                <div class="clearfix"></div>
            </div>
            <div id="collapseLogtype" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingLogtype">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-lg-4 col-lg-push-8">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <p class="text-center">
                                        Select the action type you've made on the GeoKret. For more information consult the help page.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 col-lg-pull-4 top-buffer">
                            <div class="form-group" id="infoLogtypeFormGroup">
                                <div class="col-sm-10 col-sm-offset-1">

                                    <label>
                                        <input type="radio" name="logtype" id="logType0" value="0" required>
                                        <div class="dropped box" data-toggle="tooltip" title="When you've left a GeoKret in a cache">
                                            <span>I've dropped GeoKret</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType1" value="1" required>
                                        <div class="grabbed box" data-toggle="tooltip" title="When you've taken a GeoKret from a cache and are not going to put it to another cache <i>soon</i>" data-html="true">
                                            <span>I've grabbed GeoKret</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType3" value="3" required>
                                        <div class="met box" data-toggle="tooltip" title="When you've met a GeoKret in a cache but haven't taken it with you">
                                            <span>I've met GeoKret</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType5" value="5" required>
                                        <div class="dipped box" data-toggle="tooltip" title="When you take a GeoKret for a cache-tour; this is the same as doing a drop and then grab - the visited location is logged but GeoKret is still in your inventory">
                                            <span>I've dipped a GeoKret</span>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="logtype" id="logType2" value="2" required>
                                        <div class="comment box" data-toggle="tooltip" title="When you want to write a comment :)">
                                            <span>Comment</span>
                                        </div>
                                    </label>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-3 col-sm-offset-2">
                                <button type="button" class="btn btn-primary btn-block" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseGeokret" aria-expanded="true" aria-controls="collapseGeokret">Next</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingGeokret" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseGeokret" aria-expanded="true" aria-controls="collapseGeokret">
                Identify GeoKret
                <div class="pull-right" id="geokretHeader"></div>
                <div class="clearfix"></div>
            </div>
            <div id="collapseGeokret" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingGeokret">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="nr" class="col-sm-2 control-label">Tracking Code</label>
                                <div class="col-sm-10">

                                    <div class="input-group">
                                        <input type="text" name="nr" id="nr" minlength="6" maxlength="6" required class="form-control" placeholder="eg. DQ9H4B" aria-describedby="helpBlockTrackingCode" data-parsley-trigger="input focusout" data-parsley-validation-threshold="5" data-parsley-remote data-parsley-remote-validator="checkNr" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" id="nrSearchButton">{fa icon="search"}</button>
                                        </span>
                                    </div>
                                    <span id="helpBlockTrackingCode" class="help-block tooltip_large" data-toggle="tooltip" title="<img src='{$imagesUrl}/labels/screenshots/label-screenshot.svg' style='width:100%' />" data-html="true">6 characters
                                        from <em>GeoKret label</em>. <u>Do not use the code starting with 'GK' here</u></span>

                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 alert alert-success hidden" id="nrResult">
                        </div>
                        <div class="col-sm-4 alert alert-danger hidden" id="nrError">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group top-buffer">
                            <div class="col-sm-3 col-sm-offset-2">
                                <button type="button" class="btn btn-primary btn-block" id="nextButtonNR">Next</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="panel panel-default" id="panelLocation">
            <div class="panel-heading" role="tab" id="headingLocation" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseLocation" aria-expanded="true" aria-controls="collapseLocation">
                New location
                <div class="pull-right" id="locationHeader"></div>
                <div class="clearfix"></div>
            </div>
            <div id="collapseLocation" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingLocation">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Waypoint</label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <input type="text" name="wpt" id="wpt" minlength="4" maxlength="20" class="form-control" aria-describedby="helpBlockWaypoint" placeholder="GC1AQ2N" required>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" id="wptSearchButton">{fa icon="search"}</button>
                                        </span>
                                    </div>
                                    <span id="helpBlockWaypoint" class="help-block">
                                        eg.: GC1AQ2N, OP069B, OC033A…
                                        <a href="help.php#fullysupportedwaypoints">
                                            {fa icon="question-circle"}
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group" id="findbyCacheName">
                                <label class="col-sm-2 control-label">or cache name</label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <input type="text" name="NazwaSkrzynki" id="NazwaSkrzynki" size="20" class="form-control" aria-describedby="helpBlockCacheName">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button">{fa icon="search"}</button>
                                        </span>
                                    </div>
                                    <span id="helpBlockCacheName" class="help-block">
                                        Enter cache name. Works only with opencaching networks.
                                        <a href="help.php#fullysupportedwaypoints">
                                            {fa icon="question-circle"}
                                        </a>
                                    </span>
                                </div>
                            </div>

                            <div class="col-sm-offset-2 col-sm-10 margin-bottom-20">
                                <div class="panel panel-default" id="GCcoordinatesHelp">
                                    <div class="panel-body">
                                        <p class="text-center">
                                            This is a Geocaching.com cache that no one logged on GeoKrety.org yet.
                                            To ensure correct travel of this GeoKret, please copy/paste cache coordinates
                                            in the 'Coordinates' field.
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-6">


                            <div class="panel panel-default map-togglable" id="mapField">
                                <div class="panel-heading">
                                    Coordinates
                                </div>
                                <div class="panel-body">
                                    <div class="form-group coordinates-togglable" id="coordinateField">
                                        <div class="col-sm-12">
                                            <div class="input-group">
                                                <input type="text" id="latlon" name="latlon" class="form-control" aria-describedby="helpBlockCoordinates" required>
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
                                <button type="button" class="btn btn-primary btn-block" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseMessage" aria-expanded="true" aria-controls="collapseMessage">Next</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingMessage" data-toggle="collapse" data-parent="#movePanelGroup" href="#collapseMessage" aria-expanded="true" aria-controls="collapseMessage">
                Additional data
                <div class="pull-right" id="additionalDataHeader"></div>
                <div class="clearfix"></div>
            </div>
            <div id="collapseMessage" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingMessage">
                <div class="panel-body">

                    <div class="form-group">
                        <label for="inputDate" class="col-sm-2 control-label">Date</label>
                        <div class="col-sm-6">
                            <div class="input-group date" id="datetimepicker">
                                <input type="text" class="form-control" name="data" id="inputDate" readonly required>
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Username</label>
                        <div class="col-sm-6">
                            <input type="text" name="usernameXXX" id="username" data-toggle="tooltip" data-html="true" class="form-control" title=" This may be your:<br />- geocaching/opencaching username<br />- nickname<br />- name, etc." minlength="3"
                                maxlength="20" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Comment</label>
                        <div class="col-sm-6">
                            <textarea id="poledoliczenia" name="comment" rows="6" maxlength="5120" class="form-control" aria-describedby="helpBlockComment"></textarea>
                            <span id="helpBlockComment" class="help-block">
                                It is always nice to receive a little message ;)
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>


    <div class="form-group">
        <div class="col-sm-3 col-sm-offset-2">
            <button type="button" id="submitButton" class="btn btn-primary btn-block">Post your log</button>
        </div>
    </div>
</form>
