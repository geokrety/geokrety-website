<a class="anchor" id="log{$move->id}"></a>
<div class="panel panel-default{if $move->isAuthor()} enable-dropzone dropzone{/if}" id="move-{$move->id}" data-gk-type="move" data-id="{$move->id}">
    <div class="panel-body{if $move->isAuthor()} dropzone{/if}">

        <div class="row">
            <div class="col-xs-2 move-type">
                <button type="button" class="btn btn-default btn-xs popup-move-navigate" title="{t}Show move on map{/t}" data-id="{$move->step}" {if !$move->move_type->isCoordinatesRequired()}disabled{/if}>
                    <small>#{$move->step}</small>
                </button>
                {$move|logicon nofilter}
                <small class="move-distance">{if !is_null($move->lat) and !is_null($move->lon)}{$move->distance|distance}{/if}</small>
            </div>
            <div class="col-xs-10">

                <div class="row">
                    <div class="col-xs-12">

                        <div class="pull-left move-cache">
                            {if $move->move_type->isCoordinatesRequired()}{$move->country|country nofilter}{/if}
                            {$move|cachelink nofilter}
                        </div>
                        <div class="pull-right move-author">
                            {$move->moved_on_datetime|print_date nofilter} /
                            {$move->author|userlink:$move->username nofilter}
                            {$move|application_icon nofilter}
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 move-comment">
                        {$move->comment|markdown nofilter}
                    </div>
                </div>

            </div>
        </div>

        <div class="move-pictures {if !$move->pictures_count}hidden{/if}">
            <div class="row">
                <div class="col-xs-12 gallery">
                    {if $move->pictures_count}
                    {foreach from=$move->pictures item=picture}
                        {if isset($showMainAvatarMedal)}
                            {$picture|picture:$showMainAvatarMedal nofilter}
                        {else}
                            {$picture|picture nofilter}
                        {/if}
                    {/foreach}
                    {/if}
                </div>
            </div>
        </div>

        {if !(isset($hide_actions) && hide_actions) && $f3->get('SESSION.CURRENT_USER')}
        <div class="row">
            <div class="col-xs-12">
                <div class="pull-right">
                    <div class="btn-toolbar" role="toolbar">

                        <div class="btn-group pull-right" role="group">
                            {if $move->geokret->last_position and $move->id === $move->geokret->last_position->id and $move->move_type->isTheoricallyInCache() and $move->geokret->type->getTypeId() != GeoKrety\GeokretyType::GEOKRETY_TYPE_HUMAN}
                            <button type="button" class="btn btn-danger btn-xs" title="{t}Report as missing{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment" data-id="{$move->id}" data-move-comment-type="missing">
                                {fa icon="exclamation-triangle"}
                            </button>
                            {/if}
                            {if $move->isAuthor()}
                            <button class="btn btn-success btn-xs movePictureUploadButton" title="{t}Upload a picture{/t}" data-type="move-picture-upload" data-id="{$move->id}">
                                {fa icon="plus"}&nbsp;{fa icon="picture-o"}
                            </button>
                            {/if}
                            <button type="button" class="btn btn-info btn-xs" title="{t}Write a comment{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment" data-id="{$move->id}" data-move-comment-type="comment">
                                {fa icon="plus"}&nbsp;{fa icon="comment"}
                            </button>
                        </div>

                        {if $move->isAuthor() }
                        <div class="btn-group pull-right" role="group">
                            {if $move->move_type->isEditable()}
                            <a class="btn btn-warning btn-xs" href="{'geokrety_move_edit'|alias:sprintf('@moveid=%d', $move->id)}" role="button" title="{t}Edit log{/t}" data-type="move-edit" data-id="{$move->id}">
                                {fa icon="pencil"}
                            </a>
                            {/if}
                            <button type="button" class="btn btn-danger btn-xs" title="{t}Delete log{/t}" data-toggle="modal" data-target="#modal" data-type="move-delete" data-id="{$move->id}">
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
    {if !(isset($hide_comments) && $hide_comments) and $move->comments_count}
    <ul class="list-group">
    {foreach from=$move->comments item=item}
    {include file='elements/move_comment.tpl' comment=$item}
    {/foreach}
    </ul>
    {/if}
</div>
