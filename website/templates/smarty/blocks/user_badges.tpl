<div class="panel panel-default">
  <div class="panel-heading">
    Badges
  </div>
  <div class="panel-body">
    {foreach from=$badges item=item}
    {badge infos=$item}
    {foreachelse}
    <em>{t}No badge received yet.{/t}</em>
    {/foreach}
  </div>
</div>
