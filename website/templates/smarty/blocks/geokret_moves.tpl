<a class="anchor" id="moves"></a>
{pagination total=$total_move_count perpage=MOVES_PER_PAGE anchor='moves'}
{foreach from=$moves item=item}
{call move move=$item geokret=$geokret_details moves_pictures=$geokret_pictures}
{/foreach}
{pagination total=$total_move_count perpage=MOVES_PER_PAGE anchor='moves'}
