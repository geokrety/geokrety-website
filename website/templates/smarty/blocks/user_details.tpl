<div class="panel panel-default">
    <div class="panel-heading">
        {call gk_icon gk_type=2}
        {userlink user=$user}
        <div class="btn-group pull-right" role="group">
            {if $user->email}
            <a class="btn btn-primary btn-xs" href="/majluj.php?to={$user->id}" title="{t}Send a message to the user{/t}">
                {fa icon="envelope"}
            </a>
            {/if}
            <a class="btn btn-warning btn-xs" href="/georss.php?userid={$user->id}" title="{t}Subscribe to RSS channel{/t}">
                {fa icon="rss"}
            </a>
            {if $user->isCurrentUser()}
            <a class="btn btn-info btn-xs" href="/edit.php?co=haslo" title="{t}Password change{/t}">
                {fa icon="key"}
            </a>
            <button class="btn btn-success btn-xs" title="{t}Upload a picture{/t}" data-toggle="modal" data-target="#modal" data-type="picture-upload" data-id="{$user->id}" data-picture-type="2" data-is-avatar="on">
                {fa icon="plus"}&nbsp;{fa icon="picture-o"}
            </button>
            {/if}
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">

        <div class="pull-left">
            <dl class="{if !$user_subpage}dl-horizontal{/if}">
                <dt>{t}Joined us{/t}</dt>
                <dd>{Carbon::parse($user->joinDate)->diffForHumans()}</dd>
                <dt>{t}Language{/t}</dt>
                <dd>
                    {language lang=$user->language}
                    {if $user->isCurrentUser() }
                    <div class="btn-group pull-right" role="group">
                        <button type="button" class="btn btn-warning btn-xs" title="{t}Choose prefered language{/t}" data-toggle="modal" data-target="#modal" data-type="user-choose-language">
                            {fa icon="pencil"}
                        </button>
                    </div>
                    {/if}
                </dd>
                {if $user->isCurrentUser()}
                <dt>{t}Email{/t}</dt>
                <dd>
                    {if $user->email}
                    {$user->email}
                    {else}
                    <em>{t}No email address, please add one!{/t}</em>
                    {/if}
                    <div class="btn-group pull-right" role="group">
                        <button type="button" class="btn btn-warning btn-xs" title="{t}Update email address{/t}" data-toggle="modal" data-target="#modal" data-type="user-update-email">
                            {fa icon="pencil"}
                        </button>
                    </div>
                </dd>
                <dt>{t}Secid{/t}</dt>
                <dd>
                    <div class="input-group">
                        <input class="form-control input-sm" type="text" id="secid" value="{$user->secid}" title="{t}Used to authenticate in other applications. Keep it secret!{/t}" readonly>
                        <a class="btn btn-warning btn-xs input-group-addon" href="" title="{t}Generate a new secid{/t}" data-toggle="modal" data-target="#modal" data-type="secid-refresh">
                            {fa icon="refresh"}
                        </a>
                    </div>
                </dd>
                {/if}
            </dl>

            {t escape=no}To change your username or remove your account? Send us <a href="/kontakt.php">an email</a>!{/t}
        </div>

        <div class="gallery pull-right">
            {foreach $user->avatar() as $avatar}
                {pictureOrDefault item=$avatar skipLinkToEntity=true isOwner=$user->isCurrentUser() pictureType=$avatar->type id=$user->id}
            {/foreach}
        </div>
        <div class="clearfix"></div>
    </div>


</div>
