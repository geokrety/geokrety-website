<div class="panel panel-default">
  <div class="panel-body">
    <figure>
      {$user|userstatpic nofilter}
    </figure>
    <figcaption class="caption-button">
      <p class="pull-left">
        {fa icon="link" title="{t}Copy link to page{/t}"}
        {fa icon="forumbee" title="{t}Copy to page as bbcode{/t}"}
      </p>
{if $user->isCurrentUser()}
      <p class="pull-right">
        <a class="btn btn-warning btn-xs pull-right" href="#" title="{t}Choose statpic{/t}">
          {fa icon="pencil"}
        </a>
      </p>
{/if}
    </figcaption>
  </div>
</div>
