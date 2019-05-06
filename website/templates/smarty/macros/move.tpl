{function move showActions=true}{* move= *}
<a class="anchor" id="log{$move->ruchId}"></a>
<div class="panel panel-default">
  <div class="panel-body">

    <div class="row">
      <div class="col-xs-2">
        <div class="center-block">
          {log_icon id=$move->ruchId type=$move->logType gk_type=$geokret->type}<br />
          <small>{$move->distance}&nbsp;km</small>
        </div>
      </div>
      <div class="col-xs-10">

        <div class="row">
          <div class="col-xs-12">

            <div class="pull-left">
              {country_flag country=$move->country}
              {waypoint_link wpt=$move->waypoint lat=$move->lat lon=$move->lon}
            </div>
            <div class="pull-right">
              {Carbon::parse($move->ruchData)->diffForHumans()} /
              {call userLink id=$move->userId username=$move->username}
              {application_icon app=$move->app app_ver=$move->appVer}
            </div>

          </div>
        </div>

        <div class="row">
          <div class="col-xs-12">
            {$move->comment}
          </div>
        </div>

      </div>
    </div>

    {if $move->picturesCount}
    <div class="row">
      <div class="col-xs-12">HAS PICTURE
        {call move_picture moves_pictures=$geokret_pictures}
      </div>
    </div>
    {/if}

    {if $showActions and $isLoggedIn }
    <div class="row">
      <div class="col-xs-12">
        <div class="pull-right">
          <div class="btn-toolbar" role="toolbar">

            <div class="btn-group pull-right" role="group">
              {if $move->ruchId == $geokret->lastPositionId and $move->logType|in_array:['0','3'] AND $geokret->type != '2'}
              <button type="button" class="btn btn-danger btn-xs" title="{t}Report as missing{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment" data-move-comment-type="missing" data-gkid="{$geokret->id}" data-ruchid="{$move->ruchId}">
                {fa icon="exclamation-triangle"}
              </button>
              {/if}
              {if $currentUser == $move->userId }
              <a class="btn btn-success btn-xs" href="/imgup.php?typ=1&id={$move->ruchId}" role="button" title="{t}Upload a picture{/t}">
                {fa icon="picture-o"}
              </a>
              {/if}
              <button type="button" class="btn btn-info btn-xs" title="{t}Write a comment{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment" data-gkid="{$geokret->id}" data-ruchid="{$move->ruchId}">
                {fa icon="comment"}
              </button>
            </div>

            {if isset($isGeokretOwner) and $isGeokretOwner or $currentUser == $move->userId }
            <div class="btn-group pull-right" role="group">
              <a class="btn btn-warning btn-xs" href="/ruchy.php?edit=1&ruchid={$move->ruchId}" role="button" title="{t}Edit log{/t}">
                {fa icon="pencil"}
              </a>
              <button type="button" class="btn btn-danger btn-xs" title="{t}Delete log{/t}" data-toggle="modal" data-target="#modal" data-type="move-delete" data-id="{$move->ruchId}">
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
  {if $showActions and $move->commentsCount}
  {call move_comment}
  {/if}
</div>
{/function}
