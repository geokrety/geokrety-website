<a class="anchor" id="log{$move->id}"></a>
<div class="panel panel-default">
    <div class="panel-body">

        <div class="row">
            <div class="col-xs-2">
                <div class="center-block">
                    {$move|logicon nofilter}
                    <small>{if !is_null($move->lat) and !is_null($move->lon)}{$move->distance}&nbsp;km{/if}</small>
                </div>
            </div>
            <div class="col-xs-10">

                <div class="row">
                    <div class="col-xs-12">

                        <div class="pull-left">
                            {$move->country|country nofilter}
                            {$move|cachelink}
                        </div>
                        <div class="pull-right">
                            {$move->moved_on_datetime|print_date nofilter} /
                            {$move->author|userlink:$move->username nofilter}
                            {$move|application_icon nofilter}
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        {$move->comment|markdown nofilter}
                    </div>
                </div>

            </div>
        </div>

        {if $move->pictures_count}
        <div class="row">
            <div class="col-xs-12">
                {*call move_picture moves_pictures=$geokret_pictures*}
            </div>
        </div>
        {/if}

        {if !(isset($hide_actions) && hide_actions) && $f3->get('SESSION.CURRENT_USER')}
        <div class="row">
            <div class="col-xs-12">
                <div class="pull-right">
                    <div class="btn-toolbar" role="toolbar">

                        <div class="btn-group pull-right" role="group">
                            {if $move->geokret->last_position and $move->id === $move->geokret->last_position->id and $move->logtype->isTheoricallyInCache() and $move->geokret->type->getTypeId() != \GeoKrety\GeokretyType::GEOKRETY_TYPE_HUMAN}
                            <button type="button" class="btn btn-danger btn-xs" title="{t}Report as missing{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment" data-id="{$move->id}" data-move-comment-type="missing">
                                {fa icon="exclamation-triangle"}
                            </button>
                            {/if}
                            {if $move->isAuthor()}
                            <button class="btn btn-success btn-xs" title="{t}Upload a picture{/t}" data-toggle="modal" data-target="#modal" data-type="picture-upload" data-id="{$move->id}" data-picture-type="1">
                                {fa icon="plus"}&nbsp;{fa icon="picture-o"}
                            </button>
                            {/if}
                            <button type="button" class="btn btn-info btn-xs" title="{t}Write a comment{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment" data-id="{$move->id}">
                                {fa icon="plus"}&nbsp;{fa icon="comment"}
                            </button>
                        </div>

                        {if $move->isAuthor() }
                        <div class="btn-group pull-right" role="group">
                            <a class="btn btn-warning btn-xs" href="{'geokrety_move_edit'|alias:sprintf('@moveid=%d', $move->id)}" role="button" title="{t}Edit log{/t}">
                                {fa icon="pencil"}
                            </a>
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
    {foreach from=$move->comments item=item}
    {include file='elements/move_comment.tpl' comment=$item}
    {/foreach}
    {/if}
</div>
