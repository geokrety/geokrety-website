{include file='macros/converters.tpl'}
{include file='macros/picture.tpl'}
{include file='macros/icons.tpl'}

<ol class="breadcrumb">
  <li><a href="/">{t}Home{/t}</a></li>
  <li class="active">{t}My page{/t}</li>
</ol>

<div class="row">
  <div class="col-xs-12 col-md-9">
    {if $user_subpage == 1}
    {include file='blocks/geokrety_table_owned.tpl'}
    {else if $user_subpage == 2}
    {include file='blocks/geokrety_table_watched.tpl'}
    {else if $user_subpage == 3}
    {include file='blocks/trip_table.tpl'}
    {else if $user_subpage == 4}
    <h2>{t}Recently moved GeoKrety{/t}</h2>
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
    {include file='blocks/user_map_home.tpl'}
    {include file='blocks/user_statpic.tpl'}
  </div>
</div>
