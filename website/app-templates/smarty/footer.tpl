<footer class="footer">
    <ul class="footer_bottom_ul">
        <li><a id="footer-home" href="{'home'|alias}">{t}Home{/t}</a></li>
        <li><a id="footer-help" href="{'help'|alias:null:null:'#about'}">{t}About{/t}</a></li>
        <li><a id="footer-news" href="{'news_list'|alias}">{t}News{/t}</a></li>
        <li><a id="footer-contact" href="{'contact_us'|alias}">{t}Contact{/t}</a></li>
    </ul>

    <p class="text-center">
        {t escape=no url="https://github.com/geokrety/geokrety-website/blob/master/LICENSE"}Released under <a id="footer-license" href="%1">MIT license</a>{/t}
        |
        <a href="{GK_CROWDIN_URL}" target="_blank">{t}Contribute to translation{/t}</a>
        |
{if \Multilang::instance()->current === 'inline-translation'}
        <a id="footer-inlinetranslate" href="{\Multilang::instance()->alias($f3->get('ALIAS'), $f3->get('PARAMS'), \Multilang::instance()->primary)}{if $f3->exists('GET')}?{http_build_query($f3->get('GET')) nofilter}{/if}">{t}Leave in-context translation{/t}</a>
{else}
        <a id="footer-inlinetranslate" href="{\Multilang::instance()->alias($f3->get('ALIAS'), $f3->get('PARAMS'), 'inline-translation')}{if $f3->exists('GET')}?{http_build_query($f3->get('GET')) nofilter}{/if}">{t}in-context translation{/t}</a>
{/if}
    </p>
    <p class="text-center">
        {t escape=no url={'hall_of_fame'|alias}}Designed with <abbr title="love">💗</abbr> by <a id="footer-team" href="%1">The GeoKrety Team</a>{/t}
    </p>
    <ul class="social_footer_ul text-center">
        <li><a id="footer-facebook" href="https://www.facebook.com/groups/1624761011150615/about/">{fa icon="facebook"}</a></li>
        <li><a id="footer-twitter" href="https://twitter.com/geokrety">{fa icon="twitter"}</a></li>
        <li><a id="footer-instagram" href="https://www.instagram.com/explore/tags/geokrety/">{fa icon="instagram"}</a></li>
    </ul>
    <small class="pull-right">
        {include file='elements/version.tpl'}
    </small>
</footer>
