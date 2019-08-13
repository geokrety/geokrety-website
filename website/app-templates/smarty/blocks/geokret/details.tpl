<div class="panel panel-default">
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

        <div class="btn-group pull-right" role="group">
            {if $f3->get('SESSION.CURRENT_USER') and $geokret->owner->email}
            <a class="btn btn-primary btn-xs" href="{'mail_by_geokret'|alias}" title="{t user=$geokret->owner->username}Send a message to %1{/t}">
                {fa icon="envelope"}
            </a>
            {/if}
            {if $geokret->isOwner()}
            <a class="btn btn-warning btn-xs" href="{'geokret_edit'|alias}" title="{t}Edit GeoKret details{/t}">
                {fa icon="pencil"}
            </a>
            {/if}
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-12 col-md-9">
                <dl class="dl-horizontal pull-left">
                    <dt>{t}Reference number{/t}</dt>
                    <dd>{$geokret->gkid}</dd>
                    {if $geokret->isOwner() or $geokret_already_seen}
                    <dt>{t}Tracking code{/t}</dt>
                    <dd><strong>{$geokret->tracking_code}</strong></dd>
                    {/if}
                    <dt>{t}Total distance{/t}</dt>
                    <dd>{$geokret->distance} km</dd>
                    <dt>{t}Places visited{/t}</dt>
                    <dd>{$geokret->caches_count}</dd>
                    <dt>{t}Social share{/t}</dt>
                    <dd>
                        {fa icon="facebook" title="{t}Share on Facebook{/t}"}
                        {fa icon="twitter" title="{t}Share on Twitter{/t}"}
                        {fa icon="instagram" title="{t}Share on Instagram{/t}"}
                        {fa icon="link" title="{t}Copy link to page{/t}"}
                        {fa icon="forumbee" title="{t}Copy to page as bbcode{/t}"}
                    </dd>
                    <dt>{t}Country track{/t}</dt>
                    <dd>{*country_track items=$country_track*}</dd>
                </dl>
            </div>
            <div class="col-xs-12 col-md-3 gallery">
                {*pictureOrDefault item=$geokret->avatar() skipLinkToEntity=true isOwner=$geokret->isOwner() pictureType=0 id=$geokret->id*}
                {*altitudeProfile gk_id=$geokret->id*}
            </div>
        </div>

    </div>
</div>
