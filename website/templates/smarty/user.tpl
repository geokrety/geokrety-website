{include file='macros/converters.tpl'}
{include file='macros/picture.tpl'}
{include file='macros/icons.tpl'}

<ol class="breadcrumb">
  <li><a href="">{t}Home{/t}</a></li>
{if $user->isCurrentUser()}
  <li class="active">{t}My page{/t}</li>
{else}
  <li class="active">{$user->username}</li>
{/if}
</ol>

<div class="row">
  <div class="col-xs-12 col-md-9">
    {if $user_subpage == 1}
    {include file='blocks/geokrety_table_owned.tpl'}
    {else if $user_subpage == 2}
    {include file='blocks/geokrety_table_watched.tpl'}
    {else if $user_subpage == 3}
    <a class="anchor" id="trip"></a>
    <h2>{t}Recently posted moves{/t}</h2>
    {if $trip}
      {include file='blocks/geokrety_table_trip.tpl'}
    {else}
      {if $user->isCurrentUser()}
        <em>{t}You didn't have logged any GeoKrety yet.{/t}</em>
      {else}
        <em>{t escape=no username=$user->username}%1 didn't have logged any GeoKrety yet.{/t}</em>
      {/if}
    {/if}
    {else if $user_subpage == 4}
    <a class="anchor" id="trip"></a>
    <h2>{t}Moves of owned Geokrety{/t}</h2>
    {if $trip}
      {include file='blocks/geokrety_table_trip.tpl'}
    {else}
      {if $user->isCurrentUser()}
        <em>{t}You didn't have created any GeoKrety yet or your GeoKrety never traveled yet.{/t}</em>
      {else}
        <em>{t escape=no username=$user->username}%1 didn't have created any GeoKrety yet or his GeoKrety never traveled yet.{/t}</em>
      {/if}
    {/if}
    {else if $user_subpage == 5}
    {include file='blocks/geokrety_table_inventory.tpl'}
    {else}
    {include file='blocks/user_details.tpl'}
    {include file='blocks/user_awards.tpl'}
    {include file='blocks/user_badges.tpl'}
    {/if}
  </div>
  <div class="col-xs-12 col-md-3">
    {if $user_subpage}
    {include file='blocks/user_details.tpl'}
    {/if}
    {include file='blocks/user_actions.tpl'}
    {if $user->isCurrentUser()}
    {include file='blocks/user_map_home.tpl'}
    {/if}
    {include file='blocks/user_statpic.tpl'}
  </div>
</div>
