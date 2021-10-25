<a class="anchor" id="users-awards"></a>
<div class="panel panel-default">
  <div class="panel-heading">
    {t}Awards{/t}
  </div>
  <div class="panel-body">
    {foreach from=$user->awards item=award}
    {$award|award nofilter}
    {foreachelse}
    <em>{t}No award received yet.{/t}</em>
    {/foreach}
  </div>
</div>
