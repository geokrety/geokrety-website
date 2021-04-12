{extends file='emails/base-admin.tpl'}

{block name=title}❗ Sync Cron Failure ❗{/block}
{block name=preview}{t}Something went wrong while synchronizing partners data{/t}{/block}

{block name=content}
<p>{t}Something went wrong with a cron job, please check the logs below.{/t}</p>
<block class="w-full my-2 p-2 border-3 border-gray-600 bg-gray-200">
<pre>
{foreach $errors as $partner => $messages}
  * {$partner}
{foreach $messages as $message}
    => {$message}
{/foreach}
{/foreach}
</pre>
</block>
{/block}
