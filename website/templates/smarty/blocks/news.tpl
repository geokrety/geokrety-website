<div class="panel panel-default">
  <div class="panel-heading">
    <div class="panel-title pull-left">
      <h3 class="panel-title">{$item->title}</h3>
    </div>
    <div class="panel-title pull-right">
      {newslink news=$item}
      <i>
          {$item->date} ({userlink user=$item->author()})
      </i>
    </div>
    <div class="clearfix"></div>
  </div>
  <div class="panel-body">{$item->content nofilter}</div>
</div>
