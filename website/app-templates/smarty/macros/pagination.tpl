{function pagination pg=array() anchor='top'}{if $pg->getMax() > 1}
<div class="pull-right">
    <ul class="pagination" data-gk-type="paginator" data-page-total="{$pg->getMax()}" data-page-current="{$pg->getCurrent()}">
        {if $pg->getFirst()}
            <li><a href="{$pg->getPath()}{$pg->getFirst()}#{$anchor}">{t}First{/t}</a></li>
        {/if}
        {if $pg->getPrev()}
            <li><a href="{$pg->getPath()}{$pg->getPrev()}#{$anchor}"><i class="glyphicon glyphicon-chevron-left"></i></a></li>
        {/if}
        {foreach from=$pg->getInRange() item=page}
            <li {if $page == $pg->getCurrent()}class="active"{/if}><a href="{$pg->getPath()}{$page}#{$anchor}">{$page}</a></li>
        {/foreach}
        {if $pg->getNext()}
            <li><a href="{$pg->getPath()}{$pg->getNext()}#{$anchor}"><i class="glyphicon glyphicon-chevron-right"></i></a></li>
        {/if}
        {if $pg->getLast()}
            <li><a href="{$pg->getPath()}{$pg->getLast()}#{$anchor}">{t count=$pg->getLast()}Last [%1]{/t}</a></li>
        {/if}
    </ul>
</div>
<div class="clearfix"></div>
{/if}{/function}
