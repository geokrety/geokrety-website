{function pagination pg=array() anchor='top'}{if $pg->getMax() > 1}
<div class="pull-right">
    <ul class="pagination" data-gk-type="paginator" data-page-total="{$pg->getMax()}" data-page-current="{$pg->getCurrent()}">
        {if $pg->getFirst()}
            <li><a href="{call build_url base=$pg->getPath() page=$pg->getFirst() anchor=$anchor}">{t}First{/t}</a></li>
        {/if}
        {if $pg->getPrev()}
            <li><a href="{call build_url base=$pg->getPath() page=$pg->getPrev() anchor=$anchor}"><i class="glyphicon glyphicon-chevron-left"></i></a></li>
        {/if}
        {foreach from=$pg->getInRange() item=page}
            <li {if $page == $pg->getCurrent()}class="active"{/if}><a href="{call build_url base=$pg->getPath() page=$page anchor=$anchor}">{$page}</a></li>
        {/foreach}
        {if $pg->getNext()}
            <li><a href="{call build_url base=$pg->getPath() page=$pg->getNext() anchor=$anchor}"><i class="glyphicon glyphicon-chevron-right"></i></a></li>
        {/if}
        {if $pg->getLast()}
            <li><a href="{call build_url base=$pg->getPath() page=$pg->getLast() anchor=$anchor}">{t count=$pg->getLast()}Last [%1]{/t}</a></li>
        {/if}
    </ul>
</div>
<div class="clearfix"></div>
{/if}{/function}

{function build_url base='' page=null anchor=''}
{$base}{$page}{if $f3->get('GET')}?{foreach from=$f3->get('GET') item=get}{$get@key}={$get|escape:'url'}{/foreach}{/if}#{$anchor}
{/function}
