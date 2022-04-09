{extends file='base.tpl'}

{block name=title}{t}Mass label generator{/t}{/block}

{block name=content}
<h1>{t}Mass label generator{/t}</h1>
{include file='forms/geokrety_labels.tpl'}
{/block}

{block name=javascript}
    {include file = "js/dialogs/dialog_geokret_move_select_from_inventory.tpl.js"}
    {include file = "js/moves/geokret_nr.validation.tpl.js"}
    {include file = "js/moves/geokret_move.inventory.tpl.js"}
{/block}
