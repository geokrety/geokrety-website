<div class="panel panel-default">
    <div class="panel-heading">
        {if isset($geokret) && $geokret->gkid()}
            <h3 class="panel-title">{t}Edit a GeoKret{/t}</h3>
        {else}
            <h3 class="panel-title">{t}Create a new GeoKret{/t}</h3>
        {/if}
    </div>
    <div class="panel-body">

        <form class="form-horizontal" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
            <div class="form-group">
                <label for="inputName" class="col-sm-2 control-label">{t}GeoKret name{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control maxl" id="inputName" name="name" placeholder="{t}GeoKret name{/t}" minlength="{GK_GEOKRET_NAME_MIN_LENGTH}" maxlength="{GK_GEOKRET_NAME_MAX_LENGTH}" required value="{if isset($geokret)}{$geokret->name}{/if}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputGeokretType" class="col-sm-2 control-label">{t}GeoKret type{/t}</label>
                <div class="col-sm-10">
                    <select class="form-control" id="inputGeokretType" name="type">
                        {foreach \GeoKrety\GeokretyType::getTypes() as $key => $gktype}
                            <option value="{$key}" {if isset($geokret) && $geokret->type->isType($key)} selected{/if} required>{$gktype}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="inputMission" class="col-sm-2 control-label">{t}Mission{/t}</label>
                <div class="col-sm-10">
                    <textarea class="form-control maxl" rows="5" id="inputMission" name="mission" placeholder="{t}What is this GeoKret mission?{/t}" maxlength="5120">{if isset($geokret)}{$geokret->mission}{/if}</textarea>
                </div>
            </div>

            {if (!isset($geokret) or !$geokret->gkid()) and $current_user->hasHomeCoordinates()}
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label data-toggle="tooltip" title="{t}This will create a first "dipped" move for your GeoKret, marking it's starting point.{/t}">
                            <input type="checkbox" id="logAtHome" name="log_at_home"{if $f3->get('POST.log_at_home')} checked{/if}> {t}Set my home coordinates as a starting point.{/t}
                        </label>
                    </div>
                </div>
            </div>
            {/if}

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {call csrf}
                    <button type="submit" id="createOrUpdateSubmitButton" class="btn btn-primary">{if isset($geokret) && $geokret->gkid()}{t}Save{/t}{else}{t}Create{/t}{/if}</button>
                </div>
            </div>

        </form>
    </div>
</div>
