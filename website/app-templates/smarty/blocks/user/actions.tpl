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
                <a href="{'user_inventory'|alias}">{if $user->isCurrentUser()}{t}My inventory{/t}{else}{t username=$user->username}%1's inventory{/t}{/if}</a>
            </li>
            <li>
                {fa icon="bolt"}
                <a href="{'user_owned'|alias}">{if $user->isCurrentUser()}{t}Own GeoKrety{/t}{else}{t username=$user->username}Geokrety owned by %1{/t}{/if}</a>
            </li>
            <li>
                {fa icon="bolt"}
                <a href="{'user_watched'|alias}">{if $user->isCurrentUser()}{t}My watched GeoKrety{/t}{else}{t username=$user->username}Geokrety watched by %1{/t}{/if}</a>
            </li>
            <li>
                {fa icon="plane"}
                <a href="{'user_recent_moves'|alias}">{if $user->isCurrentUser()}{t}My moves{/t}{else}{t username=$user->username}%1's moves{/t}{/if}</a>
            </li>
            <li>
                {fa icon="plane"}
                <a href="{'user_owned_recent_moves'|alias}">{if $user->isCurrentUser()}{t}Moves of my GeoKrety{/t}{else}{t username=$user->username}Moves of %1's GeoKrety{/t}{/if}</a>
            </li>
            <li>
                {fa icon="picture-o"}
                <a href="{'user_pictures'|alias}">{if $user->isCurrentUser()}{t}My posted pictures{/t}{else}{t username=$user->username}Pictures posted by %1{/t}{/if}</a>
            </li>
            <li>
                {fa icon="picture-o"}
                <a href="{'user_owned_pictures'|alias}">{if $user->isCurrentUser()}{t}Pictures of my GeoKrety{/t}{else}{t username=$user->username}Pictures of %1's GeoKrety{/t}{/if}</a>
            </li>
            <li>
                {fa icon="map"}
                <a href="{'user_owned_map'|alias}">{if $user->isCurrentUser()}{t}Map of my GeoKrety{/t}{else}{t username=$user->username}Map of %1's GeoKrety{/t}{/if}</a>
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
