{if !is_null(GK_PIWIK_URL) and $user->isCurrentUser()}
    <div class="panel panel-default">
        <div class="panel-heading">
            {t}Tracking{/t}
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-12">
                    <p>
                        {t}We collect swite usage analytics, this help us understanding how the site is used and how to enhance it.{/t}
                    </p>
                </div>
            </div>
{if filter_var(\Base::instance()->get('HEADERS.Dnt'), FILTER_VALIDATE_BOOLEAN)}
            <div class="row">
                <div class="col-sm-12">
                    <i>{t}Note: your browser has sent "DNT" header.{/t} {t}Usage will not be collected.{/t}</i>
                </div>
            </div>
{/if}
            <div class="row">
                <div class="col-sm-12">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="checkboxTrackingOptOut" {if \GeoKrety\Service\UserSettings::getForCurrentUser('TRACKING_OPT_OUT')}checked{/if}>
                                {t}Opt-out from usage analytics{/t}
                            </label>
                        </div>
                </div>
            </div>
        </div>
    </div>
{/if}
