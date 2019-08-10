{foreach from=\Flash::instance()->getMessages() item=msg}
<div class="alert alert-{$msg.status} alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="{t}Close{/t}"><span aria-hidden="true">&times;</span></button>
    {$msg.text nofilter}
</div>
{/foreach}
