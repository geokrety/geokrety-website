{extends file='base.tpl'}

{block name=title}{t}Define your observation area{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Define your observation area{/t}</h3>
    </div>
    <div class="panel-body">

        <form name="comment" action="{'user_observation_area'|alias}" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inputCoordinates">{t}Home coordinates{/t}</label>
                            <input type="text" class="form-control" id="inputCoordinates" name="coordinates" placeholder="{t}Home coordinates{/t}" value="{$currentUser->home_coordinates}" data-parsley-group="location" data-parsley-trigger="focusout" data-parsley-trigger-after-failure="focusout" data-parsley-remote data-parsley-remote-validator="checkCoordinates" data-parsley-remote-options='{ "type": "POST" }' data-parsley-errors-messages-disabled >
                        </div>

                        <p>
                            <em>{t}Use the map to select a location or enter coordinates manually.{/t}</em>
                            {fa icon="question-circle"}
                            <a href="help.php#acceptableformats">{t}Other acceptable lat/lon formats{/t}</a>
                        </p>
                        <pre class="small">
eg. 52.1534 21.0539
N 52° 09.204 E 021° 03.234
N 52° 9' 12.2400" E 21° 3' 14.0400
</pre>

                        <div class="form-group">
                            <label for="inputRadius">{t}Observation radius{/t}</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="inputRadius" name="observation_area" value="{$currentUser->observation_area|default:0}" min="0" max="{GK_USER_OBSERVATION_AREA_MAX_KM}">
                                <span class="input-group-addon">km</span>
                            </div>
                        </div>

                        <p>
                            <em>{t}Range is limited to {GK_USER_OBSERVATION_AREA_MAX_KM|distance}. Set it to 0 to disable the observation feature.{/t}</em>
                        </p>

                    </div>
                    <div class="col-md-6">
                        <div class="map-home">
                            <figure class="">
                                <div id="mapid" class="leaflet-container-200"></div>
                            </figure>
                            <figcaption>
                                <p class="text-center"><small>{t}Center the map on the zone to observe{/t}</small></p>
                            </figcaption>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                {call csrf}
                <a class="btn btn-default" href="{'user_details'|alias:sprintf('userid=%d', $currentUser->id)}" title="{t}Back to user page{/t}" data-dismiss="modal">
                    {t}Dismiss{/t}
                </a>
                <button id="userObservationAreaSubmitButton" type="submit" class="btn btn-primary">{t}Define{/t}</button>
            </div>

        </form>
    </div>
</div>
{/block}

{block name=javascript}
{include file='js/_map_init.tpl.js'}
map = initializeMap();
{if GK_DEVEL}
{* used by Tests-qa in Robot  Framework *}
$("#mapid").data({ map: map });
{/if}
{include file='js/users/user_observation_area.tpl.js'}
{/block}
