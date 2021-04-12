{extends file='emails/base-admin.tpl'}

{block name=title}❗ Cron Locked ❗{/block}
{block name=preview}{t}A script is locked{/t}{/block}

{block name=content}
<p class="text-justify">{t escape=no script=$script->name since={$script->locked_datetime|print_date_long_absolute_diff_for_humans}}Cron <strong>%1</strong> is locked since <strong>%2</strong>!{/t}</p>
{/block}
