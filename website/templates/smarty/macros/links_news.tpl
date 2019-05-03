{function newsLink}{* id= *}
{if isset($comment_count)}
{assign "text" "{t escape=no count="<span class=\"badge\">{$comment_count}</span>"}Comments %1{/t}"}
{else}
{assign "text" "news"}
{/if}
<a href="/newscomments.php?newsid={$id}">{$text nofilter}</a>{/function}
