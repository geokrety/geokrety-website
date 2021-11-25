{extends file='base.tpl'}

{block name=title}{t}GeoKrety generator result{/t}{/block}

{block name=content}
{if isset($generated_geokrety) and $generated_geokrety !== false and sizeof($generated_geokrety) > 0}
    <pre>{foreach $generated_geokrety as $line}{$line}
{/foreach}</pre>
{else}
    <em>{t}There is no results.{/t}</em>
{/if}
{/block}

{block name=javascript}
{/block}
