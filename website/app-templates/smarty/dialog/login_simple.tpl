{block name=title}API login 2 secid{/block}

{block name=content}
    {foreach from=\Flash::instance()->getMessages() item=msg}
        <div class="alert alert-{$msg.status} alert-dismissible flash-message" role="alert">
            {$msg.text nofilter}
        </div>
    {/foreach}
<form method="post">
    <label for="inputUsername">{t}Username{/t}</label>
    <input type="text" class="form-control" id="login" name="login" placeholder="{t}Username{/t}" maxlength="30"
           required autofocus>
    <label for="password">{t}Password{/t}</label>
    <input type="password" class="form-control" id="password" name="password" placeholder="{t}Password{/t}"
           maxlength="80" required>
    {call csrf}
    <button type="submit" class="btn btn-primary">{t}Sign in{/t}</button>
</form>
{/block}
