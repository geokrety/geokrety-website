{function id2gk id=0}
{"GK%04X"|sprintf:$id}
{/function}

{function gkType2Text type=0}
{if type == 0}
{t}Traditionnal{/t}
{elseif type == 1}
{t}A book/CD/DVD…{/t}
{elseif type == 2}
{t}A human{/t}
{elseif type == 3}
{t}A coin{/t}
{elseif type == 4}
{t}KretyPost{/t}
{/if}
{/function}

{function logType2Text type=0}
{if type == 0}
{t}Dropped to{/t}
{elseif type == 1}
{t}Grabbed from{/t}
{elseif type == 2}
{t}A comment{/t}
{elseif type == 3}
{t}Seen in{/t}
{elseif type == 4}
{t}Archived{/t}
{elseif type == 5}
{t}Visiting{/t}
{/if}
{/function}
