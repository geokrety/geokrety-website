
{function pagination perpage=20}
{if $total > $perpage}
<div class="pull-right">
{paginate total=$total fragment='moves'}
</div>
<div class="clearfix"></div>
{/if}
{/function}

<a class="anchor" id="moves"></a>
{pagination total=$total_move_count}
{foreach from=$moves item=item}
{call move move=$item geokret=$geokret_details moves_pictures=$geokret_pictures}
{/foreach}
{pagination total=$total_move_count}
