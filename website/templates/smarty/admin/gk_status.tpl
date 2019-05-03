<ol class="breadcrumb">
  <li><a href="/">Home</a></li>
  <li><a href="/_admin.php">Admin</a></li>
  <li class="active">GK Status</li>
</ol>

<h1>GeoKrety.org Admin</h1>

<h2>Server status</h2>
<ul>
  <li>Date and time: {'r'|date}</li>
  <li>Uptime: {'uptime'|exec}</li>
  <li>GK version: {if '../git-version'|file_exists}{'../git-version'|file_get_contents}{else}unknown{/if}</li>
</ul>

<h2>GeoKrety :: it is it!</h2>
<pre>
  {'files/statystyczka.html'|file_get_contents nofilter}
</pre>

<h2>Assessment</h2>
<ul>
  <li>Number of GK rated: {$gk_rated_count}</li>
</ul>

<h2>Logs</h2>
<ul>
  {foreach from=$log_by_app item=item}
  <li>{$item.app|default:'unknown'}: {$item.count}</li>
  {/foreach}
</ul>

{include file='macros/links_user.tpl'}
<h2>New users</h2>
<ul>
  <li>Total number of users: {$user_total}</li>
  <li>New users, last 24h: {$user_new_24h|count}</li>
  <ul>
    {foreach from=$user_new_24h item=item}
    <li>{call userLink id=$item.userid username=$item.user} ({$item.lang})</li>
    {/foreach}
  </ul>
  <li>How many users have migrated to new password hash: {($user_new_hash_count / $user_total * 100)|string_format:"%.2f"} %</li>
  <li>Online users, last 5min: {$user_online_5min|count}</li>
  <ul>
    {foreach from=$user_online_24h item=item}
    <li>{call userLink id=$item.userid username=$item.user} ({$item.lang})</li>
    {/foreach}
  </ul>
  <li>Online users, last 5min: {$user_online_5min|count}</li>
  <ul>
    {foreach from=$user_online_5min item=item}
    <li>{call userLink id=$item.userid username=$item.user} ({$item.lang})</li>
    {/foreach}
  </ul>
  <li>Online users, last 24h: {$user_online_24h|count}</li>
  <ul>
    {foreach from=$user_online_24h item=item}
    <li>{call userLink id=$item.userid username=$item.user} ({$item.lang})</li>
    {/foreach}
  </ul>
  <li>Online users, last month: {$user_online_30d|count}</li>
  <ul>
    {foreach from=$user_online_30d item=item}
    <li>{call userLink id=$item.userid username=$item.user} ({$item.lang})</li>
    {/foreach}
  </ul>
  <li>Online users, last 3 month : {$user_online_90d|count}</li>
  <ul>
    {foreach from=$user_online_90d item=item}
    <li>{call userLink id=$item.userid username=$item.user} ({$item.lang})</li>
    {/foreach}
  </ul>
  <li>Online users, last 6 month: {$user_online_180d|count}</li>
  <ul>
    {foreach from=$user_online_180d item=item}
    <li>{call userLink id=$item.userid username=$item.user} ({$item.lang})</li>
    {/foreach}
  </ul>
</ul>
