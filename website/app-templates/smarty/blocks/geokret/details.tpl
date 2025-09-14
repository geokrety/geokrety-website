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

        {if $geokret->isParked()}
            <span id="parked" title="{$geokret->parked|print_date:'c':true}">({t}Parked{/t})</span>
        {elseif !$geokret->isCollectible()}
            <span id="non-collectible" title="{$geokret->non_collectible|print_date:'c':true}">({t}Non-Collectible{/t})</span>
        {/if}

        <div class="btn-group pull-right" role="group">
            {if $f3->get('SESSION.CURRENT_USER') and $geokret->owner and $geokret->owner->email and isset($current_user) and $current_user->canSendMail()}
            <button id="userContactButton" class="btn btn-primary btn-xs" title="{t user=$geokret->owner->username}Send a message to %1{/t}" data-toggle="modal" data-target="#modal" data-type="user-contact-by-geokret" data-id="{$geokret->gkid}">
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
            <div class="col-xs-12 col-md-8">
                <dl class="dl-horizontal">
                    <dt>{t}Reference number{/t}</dt>
                    <dd title="id:{$geokret->id} gkid{$geokret->gkid()}" class="geokret-id">{$geokret->gkid}</dd>
                    {if $geokret->hasTouchedInThePast()}
                    <dt>{t}Tracking Code{/t}</dt>
                    <dd class="geokret-tracking-code"><strong>{$geokret->tracking_code}</strong></dd>
                    {/if}
                    <dt>{t}Total distance{/t}</dt>
                    <dd class="geokret-distance"><span>{$geokret->distance|distance}</span></dd>
                    <dt>{t}Places visited{/t}</dt>
                    <dd class="geokret-caches-count">{$geokret->caches_count}</dd>
                    <dt>{t}Born{/t}</dt>
                    <dd class="geokret-created-on-datetime">{$geokret->born_on_datetime|print_date nofilter}</dd>
                    {* TODO <dt>{t}Social share{/t}</dt>
                    <dd>
                        {fa icon="facebook" title="{t}Share on Facebook{/t}"}
                        {fa icon="twitter" title="{t}Share on Twitter{/t}"}
                        {fa icon="instagram" title="{t}Share on Instagram{/t}"}
                        {fa icon="link" title="{t}Copy link to page{/t}"}
                        {fa icon="forumbee" title="{t}Copy to page as bbcode{/t}"}
                    </dd>*}
                    <dt>{t}Country track{/t}</dt>
                    <dd>
                        {$geokret|country_track nofilter}
                    </dd>
                </dl>
                <div class="clearfix"></div>
                <div class="alert alert-info">
                    <h4>{t}A special GeoKret!{/t}</h4>
                    <p>
                        {t escape=false}This GeoKret is not like the others. It belongs to a secret family called <strong>Hidden GeoKrety</strong>.{/t}
                    </p>
                    <p>
                        {t}They are scattered all around the website and resources. Some appear in plain sight, waiting to be noticed. Others are deeply buried and only the most curious explorers will ever find them.{/t}
                    </p>
                    <p>
                        {t}Finding one is a reward in itself — a wink, a mystery, a little treasure of the GeoKrety world. Collect them all if you can!{/t}
                    </p>
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="pull-right picturesList">
                    <div class="gallery">
                        {if $geokret->avatar}
                            {$geokret->avatar|picture:true:false:false nofilter}
                        {/if}
                        {chart caption=_('Elevation profile') id="elevation-profile-chart" class="elevation-profile"}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
