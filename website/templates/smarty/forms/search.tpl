<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Advanced search{/t}</h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" action="{$smarty.server.PHP_SELF}" method="get">
            <div class="form-group">
                <label for="inputSearchGK" class="col-sm-2 control-label">{t}Reference Number{/t}</label>
                <div class="col-sm-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="gk" id="inputSearchGK" placeholder="GK032F" value="{if isset($smarty.get.gk)}{$smarty.get.gk}{/if}">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-default">{t}Search{/t}</button>
                        </span>
                    </div>
                </div>
            </div>
        </form>

        <form class="form-horizontal" action="{$smarty.server.PHP_SELF}" method="get">
            <!--div class="form-group">
                <label for="inputSearchNR" class="col-sm-2 control-label">{t}Tracking Code{/t}</label>
                <div class="col-sm-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="nr" id="inputSearchNR" placeholder="XF3ACS" value="{if isset($smarty.get.gk)}{$smarty.get.gk}{/if}">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-default">{t}Search{/t}</button>
                        </span>
                    </div>
                </div>
            </div-->
        </form>

        <form class="form-horizontal" action="{$smarty.server.PHP_SELF}" method="get">
            <div class="form-group">
                <label for="inputSearchName" class="col-sm-2 control-label">{t}GeoKret name{/t}</label>
                <div class="col-sm-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="nazwa" id="inputSearchName" value="{if isset($smarty.get.nazwa)}{$smarty.get.nazwa}{/if}">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-default">{t}Search{/t}</button>
                        </span>
                    </div>
                </div>
            </div>
        </form>

        <form class="form-horizontal" action="{$smarty.server.PHP_SELF}" method="get">
            <div class="form-group">
                <label for="inputSearchOwner" class="col-sm-2 control-label">{t}User name or id{/t}</label>
                <div class="col-sm-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="owner" id="inputSearchOwner" value="{if isset($smarty.get.owner)}{$smarty.get.owner}{/if}">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-default">{t}Search{/t}</button>
                        </span>
                    </div>
                </div>
            </div>
        </form>

        <form class="form-horizontal" action="{$smarty.server.PHP_SELF}" method="get">
            <div class="form-group">
                <label for="inputSearchWaypoint" class="col-sm-2 control-label">{t}Geokrety visiting the cache{/t}</label>
                <div class="col-sm-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="wpt" id="inputSearchWaypoint" placeholder="OP05E5" value="{if isset($smarty.get.wpt)}{$smarty.get.wpt}{/if}">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-default">{t}Search{/t}</button>
                        </span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{include file='macros/converters.tpl'}
{include file='macros/icons.tpl'}

{if isset($geokrety)}
<h2>{t}Search results{/t}</h2>
{include file='blocks/geokrety_table_recently_created.tpl' recent_geokrety=$geokrety}
{/if}

{if isset($users)}
<h2>{t}Found users{/t}</h2>
{foreach from=$users item=item}
{userlink user=$item}<br />
{/foreach}
{/if}
