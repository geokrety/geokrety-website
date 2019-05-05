{function move showActions=true}{* move= *}
<a class="anchor" id="log{$move.id}"></a>
<div class="panel panel-default">
  <div class="panel-body">

    <div class="row">
      <div class="col-xs-2">
        <div class="center-block">
          {log_icon id=$move.id type=$move.logtype gk_type=$geokret->type}<br />
          <small>{$move.distance}&nbsp;km</small>
        </div>
      </div>
      <div class="col-xs-10">

        <div class="row">
          <div class="col-xs-12">

            <div class="pull-left">
              {country_flag country=$move.country}
              {waypoint_link wpt=$move.waypoint lat=$move.lat lon=$move.lon}
            </div>
            <div class="pull-right">
              {Carbon::parse($move.date)->diffForHumans()} /
              {call userLink id=$move.author_id username=$move.username username2=$move.username_anonymous}
              {application_icon app=$move.app app_ver=$move.app_ver}
            </div>

          </div>
        </div>

        <div class="row">
          <div class="col-xs-12">
            {$move.comment}
          </div>
        </div>

      </div>
    </div>

    {if $move.pictures_count}
    <div class="row">
      <div class="col-xs-12">
        {call move_picture moves_pictures=$moves_pictures}
      </div>
    </div>
    {/if}

    {if $showActions and $isLoggedIn }
    <div class="row">
      <div class="col-xs-12">
        <div class="pull-right">
          <div class="btn-toolbar" role="toolbar">

            <div class="btn-group pull-right" role="group">
              {if $move.id == $geokret->lastPositionId and $move.logtype|in_array:['0','3'] AND $geokret->type != '2'}
              <button type="button" class="btn btn-danger btn-xs" title="{t}Report as missing{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment" data-move-comment-type="missing" data-gkid="{$geokret->id}" data-ruchid="{$move.id}">
                {fa icon="exclamation-triangle"}
              </button>
              {/if}
              {if $currentUser == $move.author_id }
              <a class="btn btn-success btn-xs" href="/imgup.php?typ=1&id={$move.id}" role="button" title="{t}Upload a picture{/t}">
                {fa icon="picture-o"}
              </a>
              {/if}
              <button type="button" class="btn btn-info btn-xs" title="{t}Write a comment{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment" data-gkid="{$geokret->id}" data-ruchid="{$move.id}">
                {fa icon="comment"}
              </button>
            </div>

            {if isset($isGeokretOwner) and $isGeokretOwner or $currentUser == $move.author_id }
            <div class="btn-group pull-right" role="group">
              <a class="btn btn-warning btn-xs" href="/ruchy.php?edit=1&ruchid={$move.id}" role="button" title="{t}Edit log{/t}">
                {fa icon="pencil"}
              </a>
              <button type="button" class="btn btn-danger btn-xs" title="{t}Delete log{/t}" data-toggle="modal" data-target="#modal" data-type="move-delete" data-id="{$move.id}">
                {fa icon="trash"}
              </button>
            </div>
            {/if}

          </div>
        </div>
      </div>
    </div>
    {/if}

  </div>
  {if $showActions and $move.comments_count}
  {call move_comment}
  {/if}
</div>
{/function}
