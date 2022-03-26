<div class="panel panel-default" id="userDetailsPanel">
    <div class="panel-heading">
        <img id="userDetailsTypeIcon" src="{GK_CDN_IMAGES_URL}/log-icons/2/icon_25.jpg" width="25" height="25" data-gk-type="" />
        {$user|userlink nofilter}
        <div class="btn-group pull-right" role="group">
            {if $f3->get('SESSION.CURRENT_USER') && !$user->isCurrentUser() && $user->email}
            <button id="userContactButton" class="btn btn-primary btn-xs" title="{t user=$user->username}Send a message to %1{/t}" data-toggle="modal" data-target="#modal" data-type="user-contact" data-id="{$user->id}">
                {fa icon="envelope"}
            </button>
            {/if}
            <a id="userRssFeedButton" class="btn btn-warning btn-xs" href="{'user_georss'|alias}" title="{t}Subscribe to RSS channel{/t}">
                {fa icon="rss"}
            </a>
            {if $user->isCurrentUser()}
            <a id="usernameChangeButton" class="btn btn-info btn-xs" href="{'user_update_username'|alias}" title="{t}Username change{/t}">
                {fa icon="address-card"}
            </a>
            <a id="userPasswordChangeButton" class="btn btn-info btn-xs" href="#" title="{t}Password change{/t}" data-toggle="modal" data-target="#modal" data-type="user-update-password">
                {fa icon="key"}
            </a>
            <button id="userAvatarUploadButton" class="btn btn-success btn-xs" title="{t}Upload a picture{/t}">
                {fa icon="plus"}&nbsp;{fa icon="picture-o"}
            </button>
            {/if}
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">

        <div class="pull-left">
            <dl class="dl-horizontal">
                <dt>{t}Joined us{/t}</dt>
                <dd class="user-join-on-datetime">{$user->joined_on_datetime|print_date nofilter}</dd>
                <dt>{t}Language{/t}</dt>
                <dd class="user-language">
                    {if $user->preferred_language}{$user->preferred_language|language}{else}<em>{t}Nothing selected{/t}</em>{/if}
                    {if $user->isCurrentUser() }
                    <div class="btn-group pull-right" role="group">
                        <button id="userLanguageUpdateButton" type="button" class="btn btn-warning btn-xs" title="{t}Choose preferred language{/t}" data-toggle="modal" data-target="#modal" data-type="user-choose-language">
                            {fa icon="pencil"}
                        </button>
                    </div>
                    {/if}
                </dd>
                {if $user->isCurrentUser()}
                <dt>{t}Email{/t}</dt>
                <dd class="user-email">
                    {if $user->email}
                    {$user->email}
                    {else if $user->email_activation}
                    <em>{t}Pending email validation, don't forget to click the link in validation mail!{/t}</em>
                    {else}
                    <em>{t}No email address, please add one!{/t}</em>
                    {/if}
                    <div class="btn-group pull-right" role="group">
                        <button id="userEmailUpdateButton" type="button" class="btn btn-warning btn-xs" title="{t}Update email address{/t}" data-toggle="modal" data-target="#modal" data-type="user-update-email">
                            {fa icon="pencil"}
                        </button>
                    </div>
                </dd>
                <dt>{t}Secid{/t}</dt>
                <dd class="user-secid">
                    <div class="input-group">
                        <input class="form-control input-sm" type="text" id="secid" value="{$user->secid}" title="{t}Used to authenticate in other applications. Keep it secret!{/t}" readonly>
                        <a id="userSecidUpdateButton" class="btn btn-warning btn-xs input-group-addon" href="" title="{t}Generate a new secid{/t}" data-toggle="modal" data-target="#modal" data-type="user-refresh-secid">
                            {fa icon="refresh"}
                        </a>
                    </div>
                </dd>
                {/if}
            </dl>
        </div>

        <div class="gallery pull-right">
            {$user|user_avatar nofilter}
        </div>
        <div class="clearfix"></div>
    </div>

</div>
