{function userLink}{* id, username, username2 *}
{*TODO move that to plugins and check for anonymous:https://geokrety.house.kumy.net/konkret.php?id=15368*}
<a href="/mypage.php?userid={$id}">{if $username}{$username}{elseif isset($username2) and $username2}{else}{t}Anonymous{/t}{/if}</a>{/function}
