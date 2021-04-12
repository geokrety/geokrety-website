{extends file='emails/base-admin.tpl'}

{block name=title}❗ Cron Exception ❗{/block}
{block name=preview}{t}An exception occurred in a cron job{/t}{/block}

{block name=content}
<p>{t}Something went wrong with a cron job, please check the logs below.{/t}</p>
<block class="w-full my-2 p-2 border-3 border-gray-600 bg-gray-200">
    {$errors['code']} {$errors['status']}
</block>
<block class="w-full my-2 p-2 border-3 border-gray-600 bg-gray-200">
<pre>
{$errors['text']}

{$errors['trace']}
</pre>
</block>
{/block}
