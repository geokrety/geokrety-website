{extends file='emails/base-admin.tpl'}

{block name=title}❗ Expired Accounts ❗{/block}
{block name=preview}{t}Some accounts will be deleted.{/t}{/block}

{block name=content}
<p class="text-justify">{t escape=no count=$totalExpiringTokens delay=GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY}%1 account are still not activated after %2 days. They will now be deleted.{/t}</p>
    <ul>
{foreach $expiringTokens as $token}
        <li>{$token->user->id}: {$token->user->username} {$token->expire_on_datetime|date_format nofilter} (request:{$token->requesting_ip} created on: {$token->created_on_datetime|date_format nofilter})</li>
{/foreach}
    </ul>
{/block}
