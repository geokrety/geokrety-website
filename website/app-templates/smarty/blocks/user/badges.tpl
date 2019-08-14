<div class="panel panel-default">
  <div class="panel-heading">
    {t}Badges{/t}
  </div>
  <div class="panel-body">
    {foreach from=$user->badges item=badge}
    {$badge|badge nofilter}
    {foreachelse}
    <em>{t}No badge received yet.{/t}</em>
    {/foreach}
  </div>
</div>
