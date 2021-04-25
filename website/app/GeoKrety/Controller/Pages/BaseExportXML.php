<?php

namespace GeoKrety\Controller;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidArgumentException;
use DateTimeZone;
use GeoKrety\Model\Geokret;

/**
 * @property Carbon|false modifiedSince
 */
class BaseExportXML extends BaseExport {
    protected \GeoKrety\Service\Xml\GeokretyBaseExport $xml;
    private array $filter_require_one_of = [];
    private bool $disable_filter_modifiedsince_limit = false;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->authenticate();
    }

    public function authenticate() {
        if (!$this->f3->exists('GET.secid')) {
            return;
        }
        $login = new Login();
        $login->secidAuth($this->f3, $this->f3->get('GET.secid'));
    }

    /**
     * @param $filters array|string The filter to check
     */
    public function addOneOfRequiredFilter($filters) {
        if (is_string($filters)) {
            $this->filter_require_one_of[] = $filters;
        } else {
            array_push($this->filter_require_one_of, ...$filters);
        }
        $this->filter_require_one_of = array_unique($this->filter_require_one_of);
    }

    public function _check_userid() {
        if (!$this->f3->exists('GET.userid')) {
            return;
        }
        $userid = $this->f3->get('GET.userid');
        if (strtoupper($userid) === 'NULL') {
            $userid = null;
        } elseif (!ctype_digit($userid)) {
            http_response_code(400);
            exit(_('Parameter "userid" must be a valid number.'));
        }

        // Add the filter
        if (filter_var($this->f3->get('GET.inventory'), FILTER_VALIDATE_BOOLEAN)) {
            $this->setFilter('holder = ?', $userid);
        } else {
            $this->setFilter('owner = ?', $userid);
        }
    }

    protected function setFilter(string $query, ...$params) {
        parent::setFilter($query, ...$params);
        $this->disable_filter_modifiedsince_limit = true;
    }

    public function _check_gkid() {
        if (!$this->f3->exists('GET.gkid')) {
            return;
        }
        $gkid = $this->f3->get('GET.gkid');
        if (!ctype_digit($gkid) and strtoupper(substr($gkid, 0, 2)) !== 'GK') {
            http_response_code(400);
            exit(_('Parameter "gkid" must be a valid number or GKid (GKXXXXX).'));
        }

        $id = Geokret::gkid2id($gkid);
        $this->setFilter('gkid = ?', $id);
    }

    protected function _check_wpt() {
        if (!$this->f3->exists('GET.wpt')) {
            return;
        }
        $wpt = strtoupper($this->f3->get('GET.wpt'));
        if (strlen($wpt) < 7) {
            $this->setFilter('substr(waypoint, 1, ?) = ?', strlen($wpt), $wpt);
        } else {
            $this->setFilter('waypoint = ?', $wpt);
        }
    }

    protected function checkRequiredFilter() {
        if ($this->modifiedSinceRestrictionBypass()) {
            set_time_limit(0);
        }
        foreach ($this->filter_require_one_of as $param) {
            if (array_key_exists($param, $this->f3->get('GET')) && !is_null($this->f3->get("GET.$param"))) {
                call_user_func([$this, '_check_'.$param]);
            } elseif ($param === 'coordinates') {
                $this->_check_geographic_zone();
            }
        }
        // call modifiedsince filter individually as it can be partially deactivated by using other filters
        $this->_check_modifiedsince();
        if (!$this->hasFilter() and !$this->modifiedSinceRestrictionBypass()) {
            http_response_code(400);
            echo sprintf('At least one filter is required. For more information, see %s', GK_SITE_BASE_SERVER_URL.$this->f3->alias('help_api'));
            exit();
        }
    }

    /**
     * @return bool True will bypass time restriction on modifiedsince parameter
     */
    private function modifiedSinceRestrictionBypass(): bool {
        return $this->disable_filter_modifiedsince_limit or $this->adminRestrictionBypass();
    }

    /**
     * @return bool True if bypass password is valid
     */
    private function adminRestrictionBypass(): bool {
        return $this->f3->get('GET.bypass_password') === GK_API_EXPORT_PASSWORD_BYPASS_LIMIT;
    }

    protected function _check_geographic_zone() {
        if (!($this->f3->exists('GET.latNE') and
            $this->f3->exists('GET.latSW') and
            $this->f3->exists('GET.lonNE') and
            $this->f3->exists('GET.lonSW'))) {
            return;
        }
        $coords = [
            $this->f3->get('GET.lonSW'), $this->f3->get('GET.latSW'),
            $this->f3->get('GET.lonNE'), $this->f3->get('GET.latNE'),
        ];

        // Check maximum surface
        $sql = <<<EOT
SELECT public.ST_Area(public.ST_MakeEnvelope(cast(? as float), cast(? as float), cast(? as float), cast(? as float), 4326)::public.geography) / 1000 / 1000 AS surface
EOT;  // `* 0.3048 ^ 2` for sqm?
        $result = $this->f3->get('DB')->exec($sql, [...$coords]);
        if ($result[0]['surface'] > GK_API_EXPORT_SURFACE_LIMIT and !$this->adminRestrictionBypass()) {
            exit(sprintf(_('Request surface limit exceeded: %0.3f (max: %d)'), $result[0]['surface'], GK_API_EXPORT_SURFACE_LIMIT));
        }

        $this->setFilter(
            'public.ST_Intersects(public.ST_MakeEnvelope(cast(? as float), cast(? as float), cast(? as float), cast(? as float), 4326), "position")',
            ...$coords
        );

        //## DEMO FOR https://github.com/ikkez/f3-cortex/issues/110
        //$move = new Move();
        //$move->load(['public.ST_Contains(public.ST_MakeEnvelope((?), (?), (?), (?), 4326), cast("position" as public.geometry))', 40, 0, 50, 50]);
        //die();
    }

    public function _check_modifiedsince() {
        // Check timezone
        $timezone = $this->f3->get('GET.timezone') ?: 'UTC';
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            http_response_code(400);
            exit(sprintf(_('The selected timezone is invalid "%s"'), $timezone));
        }

        if (!$this->f3->exists('GET.modifiedsince') and $this->modifiedSinceRestrictionBypass()) {
            return;
        }
        // Parse datetime
        try {
            $dateTime = Carbon::createFromFormat('YmdHis', $this->f3->get('GET.modifiedsince'), $timezone)->timezone('UTC');
            $this->modifiedSince = $dateTime;
        } catch (InvalidArgumentException $exp) {
            $dateTime = false;
        }

        // Report datetime parsing errors
        if ($dateTime === false) {
            http_response_code(400);
            $eg = Carbon::now($timezone)->subHours(2)->format('YmdHis');
            echo _('The \'modifiedsince\' parameter is missing or incorrect. It should be in YYYYMMDDhhmmss format.');
            echo '<br>';
            echo sprintf(_('Try this for data from the last 2 hours: %s'), sprintf('?modifiedsince=%d', $eg));
            echo '<p>';
            if (strtoupper($timezone) !== 'UTC') {
                echo sprintf(_('Info: Our timezone is UTC and your request is using %s, we\'ll do the conversion.'), $timezone);
            } else {
                echo _('Info: Using timezone UTC.');
            }
            echo '</p>';
            exit();
        }

        // Check days limit
        $timeElapsed = $dateTime->floatDiffInDays(null, false);
        if ($timeElapsed < 0) {
            echo sprintf(_('The requested period is %.2f days in the future.'), -$timeElapsed);
            exit();
        }
        if (!$this->modifiedSinceRestrictionBypass()) {
            $timeLimitExceeded = $timeElapsed >= GK_API_EXPORT_LIMIT_DAYS;
            if ($timeLimitExceeded) {
                http_response_code(400);
                echo sprintf(_('The requested period exceeds the %d days limit (you requested data for the past %.2f days).'), GK_API_EXPORT_LIMIT_DAYS, $timeElapsed);
                echo '<br>';
                echo sprintf(_('Please download a static version of the XML. For more information, see %s.'), GK_SITE_BASE_SERVER_URL.$this->f3->alias('help_api'));
                exit();
            }
        }

        // Add the filter
        $this->setFilter('updated_on_datetime >= ?', $this->modifiedSince->toIso8601String());
    }

    protected function processAddGeokret(&$geokret) {
        $this->xml->addGeokret($geokret);
    }

    protected function processAddMove(&$move) {
        $this->xml->addMove($move);
    }

    protected function processPostHook() {
        $this->xml->flush(true);
    }
}
