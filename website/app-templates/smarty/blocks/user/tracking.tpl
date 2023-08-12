{if !is_null(GK_PIWIK_URL) and $user->isCurrentUser()}
    <div class="panel panel-default">
        <div class="panel-heading">
            {t}Tracking{/t}
        </div>
        <div class="panel-body">
            <p>
                {t}We collect site usage analytics, this help us understanding how the site is used and how to enhance it.{/t}
            </p>
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
