<h2>{t}Select your prefered statpic background{/t}</h2>

<form class="form-horizontal" method="post">

  <div class="statpic-chooser">
    {for $statpic=1 to STATPIC_TEMPLATE_COUNT}
    <div class="radio radio-inline">
      <label>
        <input type="radio" name="statpic" value="{$statpic}" {if !is_null($user) and $user->statpic == $statpic} checked{/if}>
        <img src="{$imagesUrl}/statpics/wzory/{$statpic}.png" alt="{t id=$statpic}User statistics banner: %1{/t}" />
      </label>
    </div>
    {/for}
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">{t}Save{/t}</button>
    </div>
  </div>

</form>

<h3>{t}Your current statpic{/t}</h3>
<img src="{$imagesUrl}/statpics/{$user->id}.png" alt="{t}Current user statistics banner{/t}" />
