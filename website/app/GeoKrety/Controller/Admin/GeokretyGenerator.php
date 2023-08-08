<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\OwnerCode;
use GeoKrety\Model\User;
use GeoKrety\Service\SecretCode;
use GeoKrety\Service\Smarty;

class GeokretyGenerator extends Base {
    use \CurrentUserLoader;

    private SecretCode $secretCodeGenerator;

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $this->reassign_form_values($f3);
        list($checkSuccess, $count, $owner, $nameStartAt) = $this->check_input($f3);
        if (!$checkSuccess) {
            $this->_form_failed($f3);
        }

        $this->secretCodeGenerator = new SecretCode();
        $generated_geokrety = [$this->csvstr(['GK ID', 'GK Name', 'Tracking Code', 'Owner Code'])];
        for ($i = $nameStartAt; $i < $nameStartAt + $count; ++$i) {
            // Generate the GeoKrety
            $geokret = new Geokret();
            Smarty::assign('geokret', $geokret);
            $geokret->name = sprintf($f3->get('POST.name'), $i);
            $geokret->type = $f3->get('POST.type');
            $geokret->mission = $f3->get('POST.mission');
            $geokret->owner = $owner;
            $geokret->holder = $owner;
            $geokret->touch('created_on_datetime');
            $this->assign_tracking_code($f3, $geokret);

            if (!$geokret->validate()) {
                \Flash::instance()->addMessage(_('Something went wrong while generating GeoKrety.').' '._('Transaction aborted.'), 'danger');
                $this->_form_failed($f3);
            }
            try {
                $geokret->save();
            } catch (\Exception $e) {
                \Flash::instance()->addMessage(_('There are duplicated tracking codes.').' '._('Transaction aborted.'), 'danger');
                $this->_form_failed($f3);
            }
            $owner_code = $this->assign_owner_code($f3, $geokret);
            $generated_geokrety[] = $this->csvstr([$geokret->gkid, $geokret->name, $geokret->tracking_code, $owner_code->token]);
        }
        $f3->get('DB')->commit();

        $cache = \Cache::instance();
        $cache->set('generated_geokrety', $generated_geokrety, GK_GENERATOR_CACHE_RESULT_TTL);
        $f3->reroute('@admin_geokrety_generator_results');
    }

    public function render_results(\Base $f3) {
        $cache = \Cache::instance();
        $generated_geokrety = $cache->get('generated_geokrety');
        $cache->clear('generated_geokrety');
        Smarty::assign('generated_geokrety', $generated_geokrety);
        Smarty::render('admin/pages/geokrety_generator_result.tpl');
    }

    private function reassign_form_values(\Base $f3) {
        // Assign other values in case of errors
        foreach ($f3->get('POST') as $key => $value) {
            Smarty::assign($key, $f3->get(sprintf('POST.%s', $key)));
        }
    }

    /**
     * @return array $checkSuccess
     */
    private function check_input(\Base $f3): array {
        $checkSuccess = true;
        $count = $f3->get('POST.count');
        if (!ctype_digit($count) or $count < 1 or $count > GK_GENERATOR_MAX_COUNT) {
            \Flash::instance()->addMessage(_('The number of instances to be created is invalid.'), 'danger');
            $checkSuccess = false;
        }

        // Check Owner ID
        $owner = null;
        $ownerId = $f3->get('POST.owner');
        if (!empty($ownerId) or (ctype_digit($ownerId) and $ownerId > 0)) {
            $owner = new User();
            $owner->load(['id = ?', $ownerId]);
            if ($owner->dry()) {
                \Flash::instance()->addMessage(_('No such user found.'), 'danger');
                $checkSuccess = false;
            }
        }

        // Check start at
        $nameStartAt = $f3->get('POST.NameStartAt');
        if (!ctype_digit($nameStartAt) or $nameStartAt < 0) {
            \Flash::instance()->addMessage(_('The "start at" value is invalid.'), 'danger');
            $checkSuccess = false;
        }

        // Check TCPrefix
        $TCPrefix = $f3->get('POST.TCPrefix');
        if (in_array(strtoupper(substr($TCPrefix, 0, 2)), SecretCode::EXCLUDED_PREFIXES)) {
            \Flash::instance()->addMessage(_('The "TCPrefix" value is invalid.').' '.sprintf(_('It must not be in list "%s".'), join(', ', SecretCode::EXCLUDED_PREFIXES)), 'danger');
            $checkSuccess = false;
        }

        // Check TC length
        $TCLength = $f3->get('POST.TCLength');
        $TCSuffix = $f3->get('POST.TCSuffix');
        if (strlen($TCSuffix) + strlen($TCPrefix) >= $TCLength) {
            \Flash::instance()->addMessage(_('The "TCPrefix" + "TCSuffix" length is greater than "TCLength".'), 'danger');
            $checkSuccess = false;
        }

        return [$checkSuccess, $count, $owner, $nameStartAt];
    }

    private function _form_failed(\Base $f3) {
        $f3->get('DB')->rollback();
        $this->get($f3);
        exit;
    }

    public function get(\Base $f3) {
        Smarty::render('admin/pages/geokrety_generator.tpl');
    }

    private function assign_tracking_code(\Base $f3, Geokret $geokret) {
        try {
            $geokret->tracking_code = $this->secretCodeGenerator->generateTrackingCode(
                $f3->get('POST.TCAlphabet'),
                $f3->get('POST.TCLength'),
                $f3->get('POST.TCPrefix'),
                $f3->get('POST.TCSuffix'),
            );
        } catch (\Exception $e) {
            \Flash::instance()->addMessage(_('No more Tracking Code available using this pattern.').' '._('Transaction aborted.'), 'danger');
            $this->_form_failed($f3);
        }
    }

    private function assign_owner_code(\Base $f3, Geokret $geokret): OwnerCode {
        // Generate Owner Code
        $ownerCode = new OwnerCode();
        $ownerCode->geokret = $geokret;
        $ownerCode->token = SecretCode::generate(
            $f3->get('POST.OCAlphabet'),
            $f3->get('POST.OClength'),
            $f3->get('POST.OCPrefix'),
            $f3->get('POST.OCSuffix'),
        );
        $ownerCode->save();

        return $ownerCode;
    }

    private function csvstr(array $fields): string {
        $f = fopen('php://memory', 'r+');
        if (fputcsv($f, $fields) === false) {
            return false;
        }
        rewind($f);
        $csv_line = stream_get_contents($f);

        return rtrim($csv_line);
    }
}
