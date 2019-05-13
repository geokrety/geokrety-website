
{if !is_null($geokret) and $geokret->id}
<h2>{t}Edit a GeoKret{/t}</h2>
{else}
<h2>{t}Create a new GeoKret{/t}</h2>
{/if}

<form class="form-horizontal" method="post">
  {if !is_null($geokret) and $geokret->id}
  <input type="hidden" id="geokretId" name="id" value="{$geokret->id}">
  {/if}
  <div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">{t}GeoKret name{/t}</label>
    <div class="col-sm-10">
      <input type="text" class="form-control maxlenght" id="inputName" name="nazwa" placeholder="{t}GeoKret name{/t}" minlength="1" maxlength="45" value="{if !is_null($geokret) and $geokret->name}{$geokret->name}{/if}">
    </div>
  </div>

  <div class="form-group">
    <label for="inputGeokretType" class="col-sm-2 control-label">{t}GeoKret type{/t}</label>
    <div class="col-sm-10">
      <select class="form-control" id="inputGeokretType" name="typ">
        {foreach $geokrety_types as $key => $gktype}
        <option value="{$key}" {if !is_null($geokret) and $geokret->type == $key} selected{/if}>{$gktype}</option>
        {/foreach}
      </select>
    </div>
  </div>

  <div class="form-group">
    <label for="inputMission" class="col-sm-2 control-label">{t}Mission{/t}</label>
    <div class="col-sm-10">
      <textarea class="form-control maxlenght" rows="5" id="inputMission" name="opis" placeholder="{t}What is this GeoKrety mission?{/t}" maxlength="5120">{if !is_null($geokret) and $geokret->description}{$geokret->description}{/if}</textarea>
    </div>
  </div>

  {if !is_null($geokret) and !$geokret->id}
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <div class="checkbox">
        <label>
          <input type="checkbox" id="homeLocation" name="licznik"> {t}Set my home coordinates as a starting point.{/t}
        </label>
      </div>
    </div>
  </div>
  {/if}

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">{if !is_null($geokret) and $geokret->id}{t}Save{/t}{else}{t}Create{/t}{/if}</button>
    </div>
  </div>

</form>
