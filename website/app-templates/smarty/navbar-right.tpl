<ul class="nav navbar-nav navbar-right xs-pull-left">
    <li>
        <form id="formSearchAdvanced" class="navbar-form navbar-left" role="search" method="get" action="{'advanced_search'|alias}">
            <div class="input-group">
                <input type="search" id="inputSearchAdvanced" name="inputSearchAdvanced" class="form-control" placeholder="{t}Search…{/t}" autocomplete="off" maxlength="64">
                <div class="input-group-btn">
                    <button id="buttonSearchAdvancedType" type="submit" class="btn btn-default">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </div>
            </div>
        </form>
    </li>
    {include file="navbar-profile.tpl"}
    <hr class="hidden-sm hidden-md hidden-lg">
</ul>
