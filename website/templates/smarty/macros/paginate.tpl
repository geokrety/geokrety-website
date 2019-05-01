{function pagination perpage=20 anchor='top'}
{if $total > $perpage}
<div class="pull-right">
    {paginate total=$total anchor=$anchor pagesAroundActive=3 pagesBeforeSeparator=3 perPage=$perpage}
</div>
<div class="clearfix"></div>
{/if}
{/function}
