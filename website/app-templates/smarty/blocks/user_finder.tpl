{block name=user_finder}
    <div class="panel panel-default">
        <div class="panel-body">
            <p id="found-geokret-label">
                {t}Start by finding users:{/t}
            </p>
            <form class="form" method="get">
                <div class="form-group">
                    <input class="form-control" type="text" name="search" id="search" minlength="1" required placeholder="{t}Username or email or ID{/t}" {if isset($search)}value="{$search}"{/if}>
                </div>
                <button id="search-button" type="submit" class="btn btn-success">{t}Search{/t}</button>
            </form>
        </div>
    </div>
{/block}
