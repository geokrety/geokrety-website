<div class="panel panel-default">
  <div class="panel-heading">
    <h4>{t}Found a GeoKret?{/t}</h4>
  </div>
  <div class="panel-body">
    <p>
      {t}Please enter the tracking code here:{/t}
    </p>
    <form class="form" action="/ruchy.php" method="get">

      <div class="form-group">
        <input class="form-control input-lg" type="text" name="nr" id="nr" size="6" maxlength="6" placeholder="{t}Tracking code{/t}">
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-block">Log it!</button>
    </form>
  </div>
</div>
