<?php

use Carbon\Carbon;
use Carbon\CarbonInterval;
use GeoKrety\GeokretyType;
use GeoKrety\LogType;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Picture;
use GeoKrety\Model\User;
use GeoKrety\Service\DistanceFormatter;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Markdown;
use GeoKrety\Service\MarkdownNoImages;
use GeoKrety\Service\Url;
use GeoKrety\Service\UserBanner;
use GeoKrety\Service\WaypointInfo;

const SUPPORTED_APP = [
    'robotframework' => 'svg/robot-framework.svg',
    'c:geo' => 'svg/cgeo.svg',
    'GeoKretyLogger' => '16/GeoKrety Logger.png',
    'GeoKrety Logger' => '16/GeoKrety Logger.png',
    'GeoLog' => '16/GeoLog.png',
    'Opencaching' => '16/Opencaching.png',
    'PyGK' => '16/PyGK.png',
    'php_post' => '16/php_post.png',
];

const PARAMETERS = ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds', 'microseconds'];

function getPosIcon($id): string {
    switch ($id) {
        case 0:
            return _('Inside a cache');
        case 1:
            return _('Travelling');
        case 3:
            return _('Still in a cache');
        case 4:
            return _('Probably lost');
        case 5:
            return _('Visiting');
        case 7:
            return _('Parked');
        case 8:
            return _('In the owner hands');
        case 9:
            return _('Never Travelled');
    }

    return '';
}

function computeLogType(Geokret $geokret, ?int $locationType, ?int $lastUserId): int {
    if ($geokret->isParked()) {
        return 7;
    }
    if (is_null($locationType)) {
        return 9;
    }
    if ((($locationType === LogType::LOG_TYPE_GRABBED or $locationType === LogType::LOG_TYPE_DIPPED) and $lastUserId === (is_null($geokret->owner) ? null : $geokret->owner->id)) and $locationType !== LogType::LOG_TYPE_ARCHIVED and !($geokret->type->isType(GeokretyType::GEOKRETY_TYPE_HUMAN) or $geokret->type->isType(GeokretyType::GEOKRETY_TYPE_CAR) or $geokret->type->isType(GeokretyType::GEOKRETY_TYPE_DOG_TAG))) {
        return 8;
    }

    return $locationType;
}

function computeLocationType($logType): string {
    return $logType == '' ? '9' : $logType;
}

/**
 * Return '.png' suffix to be added to an svg image url if the output type is set to html_email.
 *
 * @param string $output The output type
 *
 * @return string The suffix to add, can be '.png' or empty string
 */
function svg2png(string $output): string {
    return $output === 'html_email' ? '.png' : '';
}

class SmartyGeokretyExtension extends Smarty\Extension\Base {
    public static function smarty_modifier_escape($string, $esc_type = 'html', $char_set = null, $double_encode = true) {
        $modifier = GeoKrety\Service\Smarty::getSmarty()->getModifierCallback('escape');

        return call_user_func($modifier, $string, $esc_type, $char_set, $double_encode);
    }

    public function getModifierCallback(string $modifierName) {
        switch ($modifierName) {
            case 'alias':
                return [$this, 'smarty_modifier_alias'];
            case 'application_icon':
                return [$this, 'smarty_modifier_application_icon'];
            case 'array_string':
                return [$this, 'smarty_modifier_array_string'];
            case 'award':
                return [$this, 'smarty_modifier_award'];
            case 'awardlink':
                return [$this, 'smarty_modifier_awardlink'];
            case 'build':
                return [$this, 'smarty_modifier_build'];
            case 'cachelink':
                return [$this, 'smarty_modifier_cachelink'];
            case 'country':
                return [$this, 'smarty_modifier_country'];
            case 'country_track':
                return [$this, 'smarty_modifier_country_track'];
            case 'date_format':
                return [$this, 'smarty_modifier_date_format'];
            case 'distance':
                return [$this, 'smarty_modifier_distance'];
            case 'gkavatar':
                return [$this, 'smarty_modifier_gkavatar'];
            case 'gkicon':
                return [$this, 'smarty_modifier_gkicon'];
            case 'gklink':
                return [$this, 'smarty_modifier_gklink'];
            case 'language':
                return [$this, 'smarty_modifier_language'];
            case 'extlink':
                return [$this, 'smarty_modifier_extlink'];
            case 'logicon':
                return [$this, 'smarty_modifier_logicon'];
            case 'login_link':
                return [$this, 'smarty_modifier_login_link'];
            case 'markdown':
                return [$this, 'smarty_modifier_markdown'];
            case 'markdown_no_images':
                return [$this, 'smarty_modifier_markdown_no_images'];
            case 'medal':
                return [$this, 'smarty_modifier_medal'];
            case 'movelink':
                return [$this, 'smarty_modifier_movelink'];
            case 'newslink':
                return [$this, 'smarty_modifier_newslink'];
            case 'picture':
                return [$this, 'smarty_modifier_picture'];
            case 'posicon':
                return [$this, 'smarty_modifier_posicon'];
            case 'print_date':
                return [$this, 'smarty_modifier_print_date'];
            case 'print_date_expiration':
                return [$this, 'smarty_modifier_print_date_expiration'];
            case 'print_date_iso_format':
                return [$this, 'smarty_modifier_print_date_iso_format'];
            case 'print_date_long_absolute_diff_for_humans':
                return [$this, 'smarty_modifier_print_date_long_absolute_diff_for_humans'];
            case 'print_interval_for_humans':
                return [$this, 'smarty_modifier_print_interval_for_humans'];
            case 'statpictemplate':
                return [$this, 'smarty_modifier_statpictemplate'];
            case 'user_avatar':
                return [$this, 'smarty_modifier_user_avatar'];
            case 'userlink':
                return [$this, 'smarty_modifier_userlink'];
            case 'userstatpic':
                return [$this, 'smarty_modifier_userstatpic'];
        }

        return null;
    }

    public function getFunctionHandler(string $functionName): ?Smarty\FunctionHandler\FunctionHandlerInterface {
        if ($functionName !== 'chart') {
            return null;
        }

        // Return a handler object that delegates to smarty_modifier_picture()
        return new class($this) implements Smarty\FunctionHandler\FunctionHandlerInterface {
            private SmartyGeokretyExtension $ext;

            public function __construct(SmartyGeokretyExtension $ext) {
                $this->ext = $ext;
            }

            public function isCacheable(): bool {
                return false;
            }

            public function handle($params, Smarty\Template $template) {
                $caption = $params['caption'] ?? '';
                $id = $params['id'] ?? ('chart-'.uniqid());
                $class = $params['class'] ?? 'alt-elevation-profile';

                // delegate to the unified picture renderer (chart path)
                return $this->ext->smarty_modifier_picture(
                    picture: null,
                    caption: $caption,
                    class: $class,
                    pictureUrl: null,
                    thumbnailUrl: null,
                    canvasDivId: $id
                );
            }
        };
    }

    /**
     * Purpose:  return the url of the given alias.
     */
    public function smarty_modifier_alias($string, $params = null, $query = null, $fragment = null): string {
        if (!is_null($fragment) && substr($fragment, 0, 1) !== '#') {
            $fragment = '#'.$fragment;
        }

        $f3 = Base::instance();

        return GK_SITE_BASE_SERVER_URL.$f3->alias($string, $params ?? [], $query).$fragment;
    }

    /**
     * Purpose:  outputs an icon for an application.
     */
    public function smarty_modifier_application_icon(GeoKrety\Model\Move $move): string {
        if (empty($move->app) || !array_key_exists($move->app, SUPPORTED_APP)) {
            return '';
        }

        $title = $move->app;
        if (!empty($move->app_ver)) {
            $title .= ' '.$move->app_ver;
        }

        return sprintf(
            '<img src="%s" title="%s" width="16">',
            sprintf('%s/api/icons/%s', GK_CDN_IMAGES_URL, SUPPORTED_APP[$move->app]),
            self::smarty_modifier_escape($title),
        );
    }

    /**
     * Purpose:  Join array elements with a string (wrapper around `implode()`).
     *
     * @throws Exception
     */
    public function smarty_modifier_array_string(string $separator, array $array): string {
        return implode($separator, $array);
    }

    /**
     * Purpose:  outputs a award image.
     *
     * @param GeoKrety\Model\Awards|GeoKrety\Model\AwardsWon $award
     *
     * @throws SmartyException
     */
    public function smarty_modifier_award($award, bool $ImageOnly = true): string {
        $template_string = <<<'EOT'
<figure>
    <img src="{$award->url}" alt="{$award->filename}" class="img-thumbnail award-badge">
    <figcaption>{$award->description}</figcaption>
</figure>
EOT;
        if ($ImageOnly) {
            $template_string = <<<'EOT'
<img src="{$award->url}" title="{$award->description}" class="award-badge" />
EOT;
        }

        $smarty = clone GeoKrety\Service\Smarty::getSmarty();
        $smarty->assign('award', $award);
        $html = $smarty->fetch('string:'.$template_string);
        $smarty->clearAssign(['award']);

        return $html;
    }

    /**
     * Purpose:  outputs a link to the award ranking.
     */
    public function smarty_modifier_awardlink(?GeoKrety\Model\Awards $award, ?string $alternative_name = null, ?string $target = null): string {
        if (is_null($award) || $award->type !== 'automatic') {
            if (!is_null($alternative_name)) {
                return self::smarty_modifier_escape($alternative_name);
            }

            return _('Unknown');
        }
        $target_html = is_null($target) ? '' : ' target="'.$target.'"';

        return sprintf(
            '<a href="%s%s" data-gk-link="award" data-gk-id="%d" title="%s"%s>%s</a>',
            GK_SITE_BASE_SERVER_URL,
            Base::instance()->alias('statistics_awards_ranking', 'award='.$award->name),
            $award->id,
            sprintf('View "%s" ranking', self::smarty_modifier_escape($award->name)),
            $target_html,
            self::smarty_modifier_escape($award->name),
        );
    }

    /**
     * Purpose:  return the url of the given alias.
     */
    public function smarty_modifier_build($string): string {
        return Base::instance()->build($string);
    }

    /**
     * Purpose:  outputs a cache link.
     */
    public function smarty_modifier_cachelink(?GeoKrety\Model\Move $move, ?string $alternative_name = null, ?string $target = 'blank'): string {
        if (is_null($move) || !$move->move_type->isCoordinatesRequired()) {
            return '';
        }

        $target = sprintf(' target="%s"', $target);

        // No waypoint → fallback to coordinates as link text
        if (empty($move->waypoint)) {
            if (!is_null($alternative_name)) {
                $alternative_name = self::smarty_modifier_escape($alternative_name);
            }

            return sprintf(
                '<a href="%s" title="%s"%s>%s</a>',
                WaypointInfo::getLinkPosition($move->lat, $move->lon),
                _('Search on geocaching.com'),
                $target,
                $alternative_name ?? $move->get_coordinates('/'),
            );
        }

        // Waypoint present → fetch cache name and build "ID — Name"
        $cacheId = self::smarty_modifier_escape($move->waypoint);
        $cacheName = $move->getWaypoint()?->name;
        $cacheName = $cacheName !== null ? self::smarty_modifier_escape($cacheName) : null;

        $linkText = $cacheName ? sprintf('%s — %s', $cacheId, $cacheName) : $cacheId;

        $titleTpl = $move->elevation > -2000 ? _('Location: %s Elevation: %dm') : _('Location: %s');
        $fullTitle = sprintf($titleTpl, $move->get_coordinates('/'), $move->elevation);

        return sprintf(
            '<a href="%s" title="%s"%s>%s</a>',
            WaypointInfo::getLink($move->waypoint),
            $fullTitle,
            $target,
            $linkText,
        );
    }

    /**
     * Purpose:  outputs a flag for a country.
     */
    /**
     * @throws Exception
     */
    public function smarty_modifier_country(?string $countryCode, string $output = 'css'): string {
        if (is_null($countryCode)) {
            $countryCode = 'xyz';
        }
        $countryCode = self::smarty_modifier_escape($countryCode);
        // TODO localize country name in title
        switch ($output) {
            case 'css':
                return sprintf('<span class="flag-icon flag-icon-%s" title="%s"></span>', strtolower($countryCode), $countryCode);
            default:
                return sprintf('<img src="%s/flags/4x3/%s.svg%s" class="w-4 d-inline-block" width="16" title="%s">', GK_CDN_SERVER_URL, $countryCode, svg2png($output), $countryCode);
        }
        throw new Exception('smarty_modifier_country(): Unsupported output mode');
    }

    /**
     * Purpose:  outputs a GeoKret country track.
     *
     * @throws SmartyException
     */
    public function smarty_modifier_country_track(?Geokret $geokret): string {
        if (is_null($geokret)) {
            return '';
        }

        $template_string = <<<'EOT'
{foreach $country_track as $country name=loop1}
    {$country.country|country nofilter}
    <small>({$country.move_count}){if not $smarty.foreach.loop1.last} &rarr; {/if}</small>
{/foreach}
EOT;

        $smarty = GeoKrety\Service\Smarty::getSmarty();
        $smarty->assign('country_track', $geokret->countryTrack());
        $html = $smarty->fetch('string:'.$template_string);

        return $html;
    }

    /**
     * Purpose:  outputs a date time according to format.
     */
    public function smarty_modifier_date_format(DateTime $date, string $format = 'c'): string {
        return $date->format($format);
    }

    /**
     * Purpose:  outputs distance according to user preferences.
     */
    public function smarty_modifier_distance(?float $distance, $unit = 'metric'): string {
        if (is_null($distance)) {
            return '';
        }

        return DistanceFormatter::format($distance, $unit);
    }

    /**
     * Purpose:  outputs a geokrety icon based if the GK has an avatar.
     */
    public function smarty_modifier_gkavatar(Geokret $geokret): string {
        if (!$geokret->avatar) {
            return '';
        }

        $iconUrl = GK_CDN_ICONS_URL.'/idcard.png';
        $alt = _('has avatar icon');
        $title = _('GeoKret has an avatar');
        $author = is_null($geokret->avatar->author) ? _('nobody') : $geokret->avatar->author->username;

        $html = <<< EOT
<a class="has-gk-avatar" href="{$geokret->avatar->url}" title="%s">
    <img src="$iconUrl" class="img-fluid w-3" width="14" height="10" alt="$alt" title="$title" />
</a>
EOT;

        return sprintf(
            $html,
            self::smarty_modifier_escape(sprintf(_('GeoKret "%s" by %s'), $geokret->name, $author)),
        );
    }

    /**
     * Purpose:  outputs a geokrety icon based on gk type.
     */
    public function smarty_modifier_gkicon(Geokret $geokret, string $output = 'html'): string {
        return sprintf(
            '<img src="%s/log-icons/%s/icon.svg%s" class="img-fluid w-3" alt="%s" title="%s" data-gk-type="%s" width="25px" height="25px">',
            GK_CDN_IMAGES_URL,
            $geokret->type->getTypeId(),
            svg2png($output),
            _('GK type icon'),
            $geokret->type->getTypeString(),
            $geokret->type->getTypeId()
        );
    }

    /**
     * Purpose:  outputs a geokret link.
     */
    public function smarty_modifier_gklink(Geokret $geokret, ?string $textString = null, ?string $target = null): string {
        $text = is_null($textString) ? $geokret->name : $textString;
        $target_html = is_null($target) ? '' : ' target="'.$target.'"';

        return sprintf(
            '<a href="%s%s" data-gk-link="geokret" data-gk-id="%d" title="%s"%s>%s</a>',
            GK_SITE_BASE_SERVER_URL,
            Base::instance()->alias('geokret_details', '@gkid='.$geokret->gkid),
            $geokret->id,
            sprintf(_('View %s\'s profile'), self::smarty_modifier_escape($geokret->name)),
            $target_html,
            self::smarty_modifier_escape($text),
        );
    }

    /**
     * Purpose:  outputs a language name based on ISO code.
     */
    public function smarty_modifier_language(?string $lang, bool $asLocale = false): string {
        return LanguageService::getLanguageByAlpha2($lang ?? 'en', $asLocale);
    }

    /**
     * Purpose:  outputs an html link.
     */
    public function smarty_modifier_extlink(string $url, ?string $textString = null, ?string $target = null): string {
        $text = is_null($textString) ? $url : self::smarty_modifier_escape($textString);
        $target_html = is_null($target) ? '' : ' target="'.$target.'"';

        return sprintf(
            '<a href="%s" title="%s"%s>%s</a>',
            $url,
            $text,
            $target_html,
            $text,
        );
    }

    /**
     * Purpose:  outputs a position icon.
     */
    public function smarty_modifier_logicon(?GeoKrety\Model\Move $move, bool $showSmall = false, string $output = 'html'): string {
        if (is_null($move)) {
            return '';
        }
        $gkType = $move->geokret->type->getTypeId();

        $url = GK_SITE_BASE_SERVER_URL.Base::instance()->alias('geokret_details_by_move_id', sprintf('gkid=%s,moveid=%d', $move->geokret->gkid, $move->id));
        $img = sprintf(
            '<img src="%s/log-icons/0/%s.svg%s" title="%s" data-gk-move-type="%s" data-gk-move-id="%s" width="%dpx" height="%dpx">',
            GK_CDN_IMAGES_URL,
            $move->move_type->getLogTypeId(),
            svg2png($output),
            sprintf('%d: %s', $move->id, $move->move_type->getLogTypeString()),
            $move->move_type->getLogTypeId(),
            $move->id,
            $showSmall ? '16' : '37',
            $showSmall ? '16' : '37',
        );

        return sprintf(
            '<a href="%s">%s</a>',
            $url,
            $img
        );
    }

    public function smarty_modifier_login_link(string $alias = 'login', $params = null): string {
        return Url::serializeGoto($alias, $params);
    }

    /**
     * Purpose:  markdown to html.
     */
    public function smarty_modifier_markdown(?string $string, ?string $mode = 'html'): string {
        if (is_null($string)) {
            return '';
        }
        if ($mode === 'html') {
            return Markdown::toHtml($string);
        }

        return Markdown::toText($string);
    }

    /**
     * Purpose:  markdown to html.
     */
    public function smarty_modifier_markdown_no_images(?string $string, ?string $mode = 'html'): string {
        if (is_null($string)) {
            return '';
        }
        if ($mode === 'html') {
            return MarkdownNoImages::toHtml($string);
        }

        return MarkdownNoImages::toText($string);
    }

    /**
     * Purpose:  outputs a medal icon.
     */
    public function smarty_modifier_medal(string $filename, string $count): string {
        $url = GK_CDN_IMAGES_URL.'/medals/'.$filename;
        $title = sprintf(_('Award for %s GeoKrety'), $count);

        return '<img src="'.$url.'" title="'.$title.'" />';
    }

    /**
     * Purpose:  outputs a move link.
     */
    public function smarty_modifier_movelink(GeoKrety\Model\Move $move, ?string $textString = null, ?string $target = null): string {
        $text = is_null($textString) ? $move->geokret->gkid : $textString;
        $target_html = is_null($target) ? '' : ' target="'.$target.'"';

        return sprintf(
            '<a href="%s%s" title="%s"%s>%s</a>',
            GK_SITE_BASE_SERVER_URL,
            Base::instance()->alias('geokret_details_paginate', ['gkid' => $move->geokret->gkid(), 'page' => $move->getMoveOnPage()], null, sprintf('log%d', $move->id)),
            _('View move details'),
            $target_html,
            self::smarty_modifier_escape($text),
        );
    }

    /**
     * Purpose:  outputs a news link.
     */
    public function smarty_modifier_newslink(GeoKrety\Model\News $news): string {
        return sprintf(
            '<span class="badge">%d</span> <a href="%s%s" ="news-link" data-id="%d">%s</a>',
            $news->comments_count,
            GK_SITE_BASE_SERVER_URL,
            Base::instance()->alias('news_details', 'newsid='.$news->id),
            $news->id,
            _('Comments')
        );
    }

    /**
     * Purpose: outputs a picture card (image OR chart).
     *
     * @throws SmartyException
     */
    public function smarty_modifier_picture(
        ?Picture $picture = null,
        ?bool $showActionsButtons = false,
        ?bool $showMainAvatarMedal = true,
        ?bool $allowSetAsMainAvatar = true,
        ?bool $showItemLink = false,
        ?bool $showPictureType = false,
        ?string $caption = null,
        ?string $class = null,
        ?string $pictureUrl = null,
        ?string $thumbnailUrl = null,
        ?string $canvasDivId = null,
    ): string {
        $smarty = GeoKrety\Service\Smarty::getSmarty();

        $isChart = !empty($canvasDivId);

        if ($picture instanceof Picture && $caption === null) {
            $caption = $picture->caption;
        }
        $caption = $caption ?? '';

        if ($isChart && !$canvasDivId) {
            $canvasDivId = 'chart-'.($picture->key ?? uniqid('chart-'));
        }

        $template_string = <<<'EOT'
<div data-gk-type="picture"
     class="{if $isChart}elevation-profile{/if}"
     {if $picture}data-picture-type="{$picture->type->getTypeId()}" data-id="{$picture->id}"{/if}>
  <figure{if $class or $isChart} class="{if $class}{$class}{/if}{if $isChart} elevation-profile{/if}"{/if}>
    <div{if $picture} id="{$picture->key}"{/if} class="parent">
      <div class="image-container{if $isChart} is-chart{/if}">
        {if $isChart}
          <svg id="{$canvasDivId}"></svg>
        {elseif $picture && !$picture->isUploaded()}
          <img src="/assets/images/the-mole-grey.svg" alt="">
          <span class="picture-message">{t}Picture is not yet ready{/t}</span>
        {elseif $thumbnailUrl}
          <a class="picture-link" href="{$pictureUrl}" data-title="{$caption|escape}">
            <img src="{$thumbnailUrl}" alt="{$caption|escape}">
          </a>
        {elseif $pictureUrl}
          <img src="{$pictureUrl}" alt="{$caption|escape}">
        {elseif $picture}
          <a class="picture-link" href="{$picture->url}" data-title="{$caption|escape}">
            <img src="{$picture->thumbnail_url}" alt="{$caption|escape}">
          </a>
        {else}
          <img src="/assets/images/the-mole-grey.svg" alt="">
        {/if}

        {if $showActionsButtons && $picture && ($picture->isAuthor()
            || ($allowSetAsMainAvatar && $picture->hasPermissionOnParent() && !$picture->isMainAvatar())) && $picture->key}
          <div class="pictures-actions" aria-label="picture actions">
            <div class="btn-group pictures-actions-buttons" role="group">
              {if $allowSetAsMainAvatar && !$picture->isMainAvatar() && $picture->hasPermissionOnParent()}
                <button class="btn btn-primary btn-xs"
                        title="{t}Define as main avatar{/t}"
                        data-toggle="modal" data-target="#modal"
                        data-type="define-as-main-avatar"
                        data-id="{$picture->key}">★</button>
              {/if}
              {if $picture->isAuthor()}
                <button class="btn btn-warning btn-xs"
                        title="{t}Edit picture details{/t}"
                        data-toggle="modal" data-target="#modal"
                        data-type="picture-edit" data-id="{$picture->key}">
                  {fa icon="pencil"}
                </button>
                <button class="btn btn-danger btn-xs"
                        title="{t}Delete picture{/t}"
                        data-toggle="modal" data-target="#modal"
                        data-type="picture-delete" data-id="{$picture->key}">
                  {fa icon="trash"}
                </button>
              {/if}
            </div>
          </div>
        {/if}
      </div>

      {if $showMainAvatarMedal && $picture && $picture->isMainAvatar()}
        <div class="picture-is-main-avatar" data-toggle="tooltip"
             title="{t}This is the main avatar{/t}"></div>
      {/if}

      {if $showPictureType && $picture}
        {if $picture->isType(Geokrety\PictureType::PICTURE_USER_AVATAR)}
          <span class="type human"></span>
        {elseif $picture->isType(Geokrety\PictureType::PICTURE_GEOKRET_MOVE)}
          <span class="type move"></span>
        {elseif $picture->isType(Geokrety\PictureType::PICTURE_GEOKRET_AVATAR)}
          <span class="type geokret"></span>
        {/if}
      {/if}
    </div>

    <figcaption>
      <p class="text-center picture-caption" title="{$caption}">{$caption}</p>
      {if $showItemLink && $picture}
        <p class="text-center">
          {if $picture->isType(Geokrety\PictureType::PICTURE_USER_AVATAR)}
            {$picture->user|userlink nofilter}
          {elseif $picture->isType(Geokrety\PictureType::PICTURE_GEOKRET_MOVE)}
            {$picture->move|movelink nofilter}
          {elseif $picture->isType(Geokrety\PictureType::PICTURE_GEOKRET_AVATAR)}
            {$picture->geokret|gklink nofilter}
          {/if}
        </p>
      {/if}
    </figcaption>
  </figure>
</div>
EOT;

        $smarty->assign([
            'picture' => $picture,
            'pictureUrl' => $pictureUrl,
            'thumbnailUrl' => $thumbnailUrl,
            'canvasDivId' => $canvasDivId,
            'caption' => $caption,
            'class' => $class ?? '',
            'isChart' => $isChart,
            'showMainAvatarMedal' => $showMainAvatarMedal,
            'showActionsButtons' => $showActionsButtons,
            'allowSetAsMainAvatar' => $allowSetAsMainAvatar,
            'showPictureType' => $showPictureType,
            'showItemLink' => $showItemLink,
        ]);

        $html = $smarty->fetch('string:'.$template_string);
        $smarty->clearAssign([
            'picture', 'pictureUrl', 'thumbnailUrl', 'canvasDivId',
            'caption', 'class', 'isChart',
            'showMainAvatarMedal', 'showActionsButtons',
            'allowSetAsMainAvatar', 'showPictureType', 'showItemLink',
        ]);

        return $html;
    }

    /**
     * Purpose:  outputs a position icon.
     */
    public function smarty_modifier_posicon(Geokret $geokret): string {
        $lastLocationType = $geokret->last_position ? $geokret->last_position->move_type->getLogTypeId() : null;
        $lastUserId = ($geokret->last_position && !is_null($geokret->last_position->author)) ? $geokret->last_position->author->id : null;

        $iconClass = computelogtype($geokret, $lastLocationType, $lastUserId);
        $message = getPosIcon($iconClass);

        return sprintf(
            '<img src="%s/log-icons/%s/1%d.png" alt="%s" title="%s" width="37" height="37" border="0" />',
            GK_CDN_IMAGES_URL,
            $geokret->type->getTypeId(),
            $iconClass,
            _('status icon'),
            $message
        );
    }

    /**
     * Purpose:  outputs a date time as relative.
     */
    public function smarty_modifier_print_date(DateTime $date, string $format = 'c', bool $raw = false): string {
        if ($raw) {
            return $date->format($format);
        }
        if (GeoKrety\Service\UserSettings::getForCurrentUser('DISPLAY_ABSOLUTE_DATE')) {
            return sprintf(
                '<span data-datetime="%s" title="%s">%s</span>',
                $date->format($format),
                $date->format($format),
                Carbon::parse($date->format('c'))->isoFormat('LLLL')
            );
        }

        return sprintf(
            '<span data-datetime="%s" title="%s">%s</span>',
            $date->format($format),
            $date->format($format),
            Carbon::parse($date->format('c'))->diffForHumans()
        );
    }

    /**
     * Purpose:  outputs a date time as longRelativeDiffForHumans
     * Doc:      https://carbon.nesbot.com/docs/#api-humandiff.
     */
    public function smarty_modifier_print_date_expiration(DateTime $date, int $parts = 3): string {
        return Carbon::instance($date)->diffForHumans(['parts' => $parts, 'join' => true]);
    }

    /**
     * Purpose:  outputs a date time as isoformat.
     *
     * @throws Exception
     *
     * @property string isoFormat
     * @property string format
     * @property string|null input_format
     * @property DateTime|string date The date to format
     */
    public function smarty_modifier_print_date_iso_format($date, string $isoFormat = 'lll', string $format = 'c', ?string $input_format = null): string {
        if (is_string($date)) {
            if (empty($input_format)) {
                throw new Exception('When date is a string, input_format must be specified');
            }
            $date = DateTime::createFromFormat($input_format, $date);
        }

        return sprintf(
            '<span data-datetime="%s" title="%s">%s</span>',
            $date->format($format),
            $date->format($format),
            Carbon::parse($date->format('c'))->isoFormat($isoFormat)
        );
    }

    /**
     * Purpose:  outputs a date time as longAbsoluteDiffForHumans
     * Doc:      https://carbon.nesbot.com/docs/#api-humandiff.
     */
    public function smarty_modifier_print_date_long_absolute_diff_for_humans(DateTime $date, int $parts = 3): string {
        return Carbon::instance($date)->longAbsoluteDiffForHumans(['parts' => $parts]);
    }

    /**
     * Purpose:  outputs an interval as human readable text.
     *
     * @throws Exception
     */
    public function smarty_modifier_print_interval_for_humans(string $unit, int $value): string {
        if (!in_array($unit, PARAMETERS)) {
            throw new Exception('Invalid unit for print_interval_for_humans');
        }
        $years = $months = $weeks = $days = $hours = $minutes = $seconds = $microseconds = 0;
        $$unit = $value;
        $interval = CarbonInterval::create($years, $months, $weeks, $days, $hours, $minutes, $seconds, $microseconds);

        return $interval->cascade()->forHumans();
    }

    /**
     * Purpose:  outputs a geokret link.
     */
    public function smarty_modifier_statpictemplate(int $statpic_template): string {
        return sprintf(
            '<img src="/app-ui/statpics/templates/%d.png" class="img-responsive center-block"  alt="%s" />',
            $statpic_template,
            sprintf(_('User statistics banner: %s'), $statpic_template)
        );
    }

    /**
     * Purpose:  outputs a user_avatar image via libravatar service.
     *
     * @throws SmartyException
     */
    public function smarty_modifier_user_avatar(User $user): string {
        if (!$user->avatar) {
            $url = Base::instance()->alias('user_avatar');

            return $this->smarty_modifier_picture(pictureUrl: $url, caption: _('Profile avatar'));
        }

        return $this->smarty_modifier_picture($user->avatar, true);
    }

    /**
     * Purpose:  outputs a user link.
     */
    public function smarty_modifier_userlink(?User $user, ?string $alternative_name = null, ?string $target = null): string {
        $target_html = is_null($target) ? '' : ' target="'.$target.'"';
        if (is_null($user) || !$user->id) {
            $username = _('Anonymous');
            if (!is_null($alternative_name)) {
                $username = self::smarty_modifier_escape($alternative_name);
            }

            return sprintf('<em class="author user-anonymous">%s</em>', $username);
        }

        return sprintf(
            '<a href="%s%s" class="author" data-gk-link="user" data-gk-id="%d" title="%s"%s>%s</a>',
            GK_SITE_BASE_SERVER_URL,
            Base::instance()->alias('user_details', 'userid='.$user->id),
            $user->id,
            sprintf('View %s\'s profile', self::smarty_modifier_escape($user->username)),
            $target_html,
            self::smarty_modifier_escape($user->username),
        );
    }

    /**
     * Purpose:  outputs a geokret link.
     */
    public function smarty_modifier_userstatpic(User $user): string {
        return sprintf(
            '<img id="statPic" src="%s" class="img-responsive center-block" title="%s" />',
            UserBanner::get_banner_url($user),
            sprintf(_('%s\'s statpic'),
                self::smarty_modifier_escape($user->username))
        );
    }
}
