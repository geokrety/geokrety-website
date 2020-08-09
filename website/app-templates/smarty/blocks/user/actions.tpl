{if $user->isCurrentUser()}
<div class="panel panel-default">
    <a href="{'geokret_create'|alias}" id="userProfileCreateGeokretButton" class="btn btn-success btn-block"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> {t}Create a new GeoKret{/t}</a>
</div>
{/if}

<div class="panel panel-default">
    <div class="panel-body">
        <ul class="links">
            <li>
                {fa icon="briefcase"}
                <a href="{'user_inventory'|alias}">{t}View inventory{/t}</a>
            </li>
            <li>
                {fa icon="bolt"}
                <a href="{'user_owned'|alias}">{t}Owned GeoKrety{/t}</a>
            </li>
            <li>
                {fa icon="bolt"}
                <a href="{'user_watched'|alias}">{t}Watched GeoKrety{/t}</a>
            </li>
            <li>
                {fa icon="plane"}
                <a href="{'user_recent_moves'|alias}">{t}Recently posted moves{/t}</a>
            </li>
            <li>
                {fa icon="plane"}
                <a href="{'user_owned_recent_moves'|alias}">{t}Moves of owned GeoKrety{/t}</a>
            </li>
            <li>
                {fa icon="picture-o"}
                <a href="{'user_pictures'|alias}">{t}Posted pictures{/t}</a>
            </li>
            <li>
                {fa icon="picture-o"}
                <a href="{'user_owned_pictures'|alias}">{t}Owned GeoKrety pictures{/t}</a>
            </li>
            <li>
                {fa icon="map"}
                <a href="{'user_owned_map'|alias}">{t}Where are my GeoKrety?{/t}</a>
            </li>
            {* TODO
            <li>
                {fa icon="bar-chart-o"}
                <a href="{'user_statistics'|alias}">{t}User stats{/t}</a>
            </li>
            *}
        </ul>
    </div>
</div>
