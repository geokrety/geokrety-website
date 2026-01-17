<div class="panel panel-default">
    <div class="panel-heading">
        {if isset($geokret) and $geokret->gkid()}
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

        {if isset($geokret) and $geokret->gkid()}
            <div class="form-group">
                <label for="born_on_datetime_localized" class="col-sm-2 control-label">{t}Birth date{/t}</label>
                <div class="col-sm-6">
                    <div class="input-group date" id="datetimepicker">
                        <input type="text" class="form-control" name="born_on_datetime_localized" id="born_on_datetime_localized" required data-parsley-datebeforenow="L LT" data-parsley-trigger="focusout" data-parsley-trigger-after-failure="focusout" />
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    </div>
                    <input type="hidden" name="born_on_datetime" id="born_on_datetime" />
                </div>
            </div>
        {/if}

            <div class="form-group">
                <label for="inputGeokretType" class="col-sm-2 control-label">{t}GeoKret type{/t}</label>
                <div class="col-sm-10">
                    <select class="form-control" id="inputGeokretType" name="type">
                        {foreach \GeoKrety\GeokretyType::getTypes() as $key => $gktype}
                            {if !\GeoKrety\GeokretyType::typeIsAdminOnly($key) || $currentUser->isAdmin() }
                            <option value="{$key}" {if isset($geokret) and $geokret->type->isType($key)} selected{/if} required>{$gktype}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>

        {if isset($geokret) and $geokret->gkid()}
            <div class="form-group">
                <label for="checkboxCollectible" class="col-sm-2 control-label">{t}Collectible{/t}</label>
                <div class="col-sm-10">
                  <div class="checkbox">
                    <label>
                        <input type="checkbox" id="checkboxCollectible" name="collectible" {if $geokret->isCollectible()}checked{/if} aria-describedby="helpCheckboxCollectible">
                    </label>
                  </div>
                <p id="helpCheckboxCollectible" class="help-block">
                    {t}Non-Collectible GeoKrety will only allow a limited kind of move types.{/t}
                    {t}Other users will not be able to collect them.{/t}
                </p>
                </div>
            </div>
        {/if}

        {if isset($geokret) and $geokret->gkid() and $geokret->isHolder() and $geokret->isOwner()}
            <div class="form-group">
                <label for="checkboxParked" class="col-sm-2 control-label">{t}Parked{/t}</label>
                <div class="col-sm-10">
                  <div class="checkbox">
                    <label>
                        <input type="checkbox" id="checkboxParked" name="parked" {if $geokret->isParked()}checked{/if} aria-describedby="helpCheckboxParked">
                    </label>
                  </div>
                <p id="helpCheckboxParked" class="help-block">
                    {t}Parked GeoKrety will automatically imply non-collectible.{/t}
                    {t}They will not appear in your inventory.{/t}
                </p>
                </div>
            </div>

            <div class="form-group">
                <label for="checkboxCommentsHidden" class="col-sm-2 control-label">{t}Hide comments{/t}</label>
                <div class="col-sm-10">
                  <div class="checkbox">
                    <label>
                        <input type="checkbox" id="checkboxCommentsHidden" name="comments_hidden" {if $geokret->areCommentsHidden()}checked{/if} aria-describedby="helpCheckboxCommentsHidden">
                    </label>
                  </div>
                <p id="helpCheckboxCommentsHidden" class="help-block">
                    {t}All move comments will be hidden by default.{/t}
                    {t}Users will not see them unless they choose to.{/t}
                </p>
                </div>
            </div>
        {/if}

            <div class="form-group">
                <label for="inputMission" class="col-sm-2 control-label">
                    {if isset($geokret) and $geokret->gkid()}
                    <a type="button" class="btn btn-xs" title="{t}View legacy mission{/t}" data-toggle="modal" data-target="#modal" data-type="geokret-legacy-mission" data-id="{$geokret->gkid()}">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </a>
                    {/if}
                    {t}Mission{/t}
                </label>
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

            {include file='elements/label_form_common.tpl'}

        </form>
    </div>
</div>

{include file='elements/label_preview.tpl'}
