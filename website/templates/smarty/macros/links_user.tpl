{function userLink}{* id, username, username2 *}
<a href="/mypage.php?userid={$id}">{if $username}{$username}{elseif $username2}{else}{t}Anonymous{/t}{/if}</a>{/function}
