<div class="panel panel-default">
  <div class="panel-heading">
    <div class="panel-title pull-left">
      <h3 class="panel-title">{$item.title}</h3>
    </div>
    <div class="panel-title pull-right">
      {call newsLink id=$item.news_id comment_count=$item.comment_count}
      <i>
        {$item.date} ({if $item.userid == 0}{$item.who}{else}{call userLink id=$item.userid username=$item.who}{/if})
      </i>
    </div>
    <div class="clearfix"></div>
  </div>
  <div class="panel-body">{$item.content nofilter}</div>
</div>
