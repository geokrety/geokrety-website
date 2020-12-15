<div class="panel panel-default" id="geokretyDetailsPanel">
    <div class="panel-heading">
        {$geokret|gkicon nofilter}

        {if $geokret->owner}
            {t escape=no
            gk_name={$geokret|gklink nofilter}
            gk_type={$geokret->type}
            username={$geokret->owner|userlink nofilter}
        }%1 <small>(%2)</small> by %3{/t}
        {else}
            {t escape=no
            gk_name={$geokret|gklink nofilter}
            gk_type={$geokret->type}
            url={'geokret_claim'|alias}
        }%1 <small>(%2)</small> - <a href="%3">Ready for adoption</a>{/t}
        {/if}

        {if $geokret->isMissing()}
            - {t}missing{/t}
        {elseif $geokret->isArchived()}
            - {t}archived{/t}
        {/if}

        <div class="btn-group pull-right" role="group">
            {if $f3->get('SESSION.CURRENT_USER') and $geokret->owner and $geokret->owner->email}
            <button class="btn btn-primary btn-xs" title="{t user=$geokret->owner->username}Send a message to %1{/t}" data-toggle="modal" data-target="#modal" data-type="user-contact-by-geokret" data-id="{$geokret->gkid}">
                {fa icon="envelope"}
            </button>
            {/if}
            {if $geokret->isOwner()}
            <a class="btn btn-warning btn-xs" href="{'geokret_edit'|alias}" title="{t}Edit GeoKret details{/t}">
                {fa icon="pencil"}
            </a>
            <button id="geokretAvatarUploadButton" class="btn btn-success btn-xs" title="{t}Upload a picture{/t}">
                {fa icon="plus"}&nbsp;{fa icon="picture-o"}
            </button>
            {/if}
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body {if $geokret->isMissing()}panel-body-danger{elseif $geokret->isArchived()}panel-body-default{/if}">
        <div class="row">
            <div class="col-xs-12 col-md-9">
                <dl class="dl-horizontal pull-left">
                    <dt>{t}Reference number{/t}</dt>
                    <dd title="id:{$geokret->id} gkid{$geokret->gkid()}" class="geokret-id">{$geokret->gkid}</dd>
                    {if $geokret->isOwner() or $geokret->hasTouchedInThePast()}
                    <dt>{t}Tracking Code{/t}</dt>
                    <dd class="geokret-tracking-code"><strong>{$geokret->tracking_code}</strong></dd>
                    {/if}
                    <dt>{t}Total distance{/t}</dt>
                    <dd class="geokret-distance"><span>{$geokret->distance|distance}</span></dd>
                    <dt>{t}Places visited{/t}</dt>
                    <dd class="geokret-caches-count">{$geokret->caches_count}</dd>
                    <dt>{t}Born{/t}</dt>
                    <dd class="geokret-created-on-datetime">{$geokret->created_on_datetime|print_date nofilter}</dd>
                    {* TODO <dt>{t}Social share{/t}</dt>
                    <dd>
                        {fa icon="facebook" title="{t}Share on Facebook{/t}"}
                        {fa icon="twitter" title="{t}Share on Twitter{/t}"}
                        {fa icon="instagram" title="{t}Share on Instagram{/t}"}
                        {fa icon="link" title="{t}Copy link to page{/t}"}
                        {fa icon="forumbee" title="{t}Copy to page as bbcode{/t}"}
                    </dd>
                    <dt>{t}Country track{/t}</dt>
                    <dd>{country_track items=$country_track}</dd>*}
                </dl>
            </div>
            <div class="col-xs-12 col-md-3">
                <div class="pull-right picturesList">
                    {if $geokret->avatar}
                        {$geokret->avatar|picture:true:false:false nofilter}
                    {/if}
                    {'/assets/images/placeholder-graph.png'|url_picture:'/assets/images/placeholder-graph.png' nofilter}{* TODO: Altitude profile *}
                </div>
            </div>
        </div>

    </div>
</div>
