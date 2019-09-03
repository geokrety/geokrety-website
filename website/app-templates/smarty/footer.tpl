<footer class="footer">
    <ul class="footer_bottom_ul">
        <li><a href="{'home'|alias}">{t}Home{/t}</a></li>
        <li><a href="{'help'|alias:null:null:'#about'}">{t}About{/t}</a></li>
        <li><a href="{'news_list'|alias}">{t}News{/t}</a></li>
        <li><a href="{'contact_us'|alias}">{t}Contact{/t}</a></li>
    </ul>

    <p class="text-center">
        {t escape=no url="https://github.com/geokrety/geokrety-website/blob/master/LICENSE"}Released under <a href="%1">MIT license</a>{/t}
        |
{if \Multilang::instance()->current === 'inline-translation'}
        <a href="{GK_SITE_BASE_SERVER_URL}">{t}Leave in-context translation{/t}</a>
{else}
        <a href="{GK_SITE_BASE_SERVER_URL}/inline-translation">{t}in-context translation{/t}</a>
{/if}
    </p>
    <p class="text-center">
        {t escape=no url={'hall_of_fame'|alias}}Designed with <abbr title="love">💗</abbr> by <a href="%1">The GeoKrety Team</a>{/t}
    </p>
    <ul class="social_footer_ul text-center">
        <li><a href="https://www.facebook.com/groups/1624761011150615/about/">{fa icon="facebook"}</a></li>
        <li><a href="https://twitter.com/geokrety">{fa icon="twitter"}</a></li>
        <li><a href="https://www.instagram.com/explore/tags/geokrety/">{fa icon="instagram"}</a></li>
    </ul>
    <small class="pull-right">
        <span class="deployment" title="{t date=GK_DEPLOY_DATE}Deployed on %1{/t}" data-deploy-date="{GK_DEPLOY_DATE}" data-deploy-version="{GK_APP_VERSION}" data-deploy-name="{GK_INSTANCE_NAME}" data-deploy-environment="{GK_ENVIRONMENT}">GK
            {GK_APP_VERSION} - {GK_INSTANCE_NAME}.{GK_ENVIRONMENT}</span>&nbsp;
    </small>
</footer>
