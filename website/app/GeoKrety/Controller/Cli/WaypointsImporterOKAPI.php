<?php

namespace GeoKrety\Controller\Cli;

use Exception;
use GeoKrety\Model\WaypointOC;
use GeoKrety\Model\WaypointSync;
use GeoKrety\Service\ConsoleWriter;
use GeoKrety\Service\File;
use PDOException;

class WaypointsImporterOKAPI extends WaypointsImporterBase {
    const OKAPI_CHANGELOG_ENDPOINT = '/okapi/services/replicate/changelog';
    const OKAPI_FULL_DUMP_ENDPOINT = '/okapi/services/replicate/fulldump';

    const SCRIPT_NAME = 'waypoint_importer_okapi';
    public int $nTotal = 0;
    public int $nUpdated = 0;
    public int $nDeleted = 0;
    public int $nSkipped = 0;
    public int $nError = 0;
    private ConsoleWriter $console_writer;
    private string $mPart = 'n/a';

    public function process() {
        $this->start();
        $this->console_writer = new ConsoleWriter('Importing OKAPI %7s: %6.2f%% (%s/%d) - U:%d D:%d S:%d E:%d');
        foreach (GK_OKAPI_PARTNERS as $okapi => $params) {
            try {
                $this->process_okapi($okapi, $params);
            } catch (Exception $exception) {
                echo sprintf("\e[0;31mE: %s\e[0m", $exception->getMessage()).PHP_EOL;
            }
        }
        parent::end();
    }

    /**
     * Start OKAPI import.
     *
     * @param string $okapi  Okapi service name
     * @param array  $params Okapi parameters
     *
     * @throws Exception
     */
    private function process_okapi(string $okapi, array $params) {
        echo sprintf("** \e[0;32mProcessing OKAPI: %s\e[0m", $okapi).PHP_EOL;

        $okapiSync = new WaypointSync();
        $okapiSync->load(['service_id = ?', $okapi]);
        if ($okapiSync->dry() or is_null($okapiSync->last_update)) {
            $this->process_okapi_full($okapi, $params);
        } else {
            $this->process_okapi_incremental($okapi, $okapiSync->last_update, $params);
        }
        echo PHP_EOL;
    }

    /**
     * OKAPI import from full dump.
     *
     * @param string $okapi  Okapi service name
     * @param array  $params Okapi parameters
     *
     * @throws Exception
     */
    private function process_okapi_full(string $okapi, array $params) {
        echo "*** \e[0;33mRunning Full Import\e[0m".PHP_EOL;
        ob_flush();
        $tmp_file = tmpfile();
        $path = stream_get_meta_data($tmp_file)['uri'];
        //$tmpdir = File::tmpdir();
        $tmpdir = '/tmp/tmp_504366139';

        try {
            $url_params = http_build_query([
                'pleeaase' => 'true',
                'consumer_key' => $params['key'],
            ]);
            //File::download(sprintf("%s%s?%s", $params['url'], self::OKAPI_FULL_DUMP_ENDPOINT, $url_params), $path);
            //File::extract_tar($path, $tmpdir);

            $index = json_decode(file_get_contents($tmpdir.'/index.json'));
            $totalFiles = sizeof($index->data_files);
            foreach ($index->data_files as $piece) {
                $changes = json_decode(file_get_contents($tmpdir.'/'.$piece));
                $this->mPart = sprintf('%s/%d', $piece, $totalFiles);
                $this->perform_incremental_update($okapi, $changes);
            }
            $this->save_last_update($okapi, $index->revision);
        } finally {
            //File::deleteTree($tmpdir);
        }
    }

    /**
     * Process json dumps and update our database.
     *
     * @param string $okapi   Okapi service name
     * @param array  $changes The changes to be processed
     */
    private function perform_incremental_update(string $okapi, array $changes) {
        $this->nTotal += sizeof($changes);
        foreach ($changes as $change) {
            if ($change->object_type != 'geocache') {
                ++$this->nSkipped;
                $this->print_stats();
                continue;
            }
            $id = $this->string_cleaner($change->object_key->code);

            if ($change->change_type == 'delete') {
                //delete from DB
                $wpt = new WaypointOC();
                $wpt->erase(['waypoint = ?', $id]);
                ++$this->nDeleted;
                $this->print_stats();
                continue;
            }

            $wpt = new WaypointOC();
            $wpt->load(['waypoint = ?', $id]);
            $wpt->waypoint = $id;
            $wpt->provider = $okapi;

            if (isset($change->data->names)) {
                $wpt->name = $this->string_cleaner(implode(' | ', (array) $change->data->names));
            }
            if (isset($change->data->owner->username)) {
                $wpt->owner = $this->string_cleaner($change->data->owner->username);
            }
            if (isset($change->data->location)) {
                $location = explode('|', $change->data->location);
                $wpt->lat = number_format(floatval($location[0]), 5, '.', '');
                $wpt->lon = number_format(floatval($location[1]), 5, '.', '');
            }
            if (isset($change->data->type)) {
                $wpt->type = $this->string_cleaner($change->data->type);
            }
            if (isset($change->data->url)) {
                $wpt->link = (string) $change->data->url;
            }
            if (isset($change->data->status)) {
                $wpt->status = $this->status_to_id($change->data->status);
            }
            try {
                $wpt->save();
            } catch (PDOException $exception) {
                ++$this->nError;
                $this->print_stats();
                continue;
            }
            ++$this->nUpdated;
            $this->print_stats();
        }
    }

    private function print_stats() {
        $nProcessed = $this->nUpdated + $this->nDeleted + $this->nSkipped + $this->nError;
        $nPercent = $nProcessed / $this->nTotal * 100;
        $this->console_writer->print([$this->mPart, $nPercent, $nProcessed, $this->nTotal, $this->nUpdated, $this->nDeleted, $this->nSkipped, $this->nError]);
    }

    protected function status_to_id(string $status, ?string $subtype = null): ?int {
        switch ($status) {
            case 'Available':
                return 1;
            case 'Temporarily unavailable':
                return 2;
            case 'Archived':
                return 3;
            default:
                return null;
        }
    }

    //private function save_last_update(string $okapi, int $revision) {
    //    $okapiSync = new WaypointSync();
    //    $okapiSync->load(['service_id = ?', $okapi]);
    //    $okapiSync->service_id = $okapi;
    //    $okapiSync->last_update = $revision;
    //    $okapiSync->save();
    //}

    /**
     * @param string $okapi    The currently processing OKAPI service
     * @param int    $revision The last know revision
     * @param array  $params   Provider parameters
     *
     * @throws Exception When something goes wrong
     */
    private function process_okapi_incremental(string $okapi, int $revision, array $params) {
        echo sprintf("*** \e[0;33mRunning Incremental Import from: %d\e[0m", $revision).PHP_EOL;
        ob_flush();
        $tmp_file = tmpfile();
        $path = stream_get_meta_data($tmp_file)['uri'];
        $more = true;

        // Since amount of results per revision is limited, need to iterate quite a lot of times for big OC websites.
        while ($more) {
            $url_params = http_build_query([
                'consumer_key' => $params['key'],
                'since' => $revision,
            ]);
            File::download(sprintf('%s%s?%s', $params['url'], self::OKAPI_CHANGELOG_ENDPOINT, $url_params), $path);
            $raw = file_get_contents($path);
            if ($raw === false) {
                throw new Exception(sprintf('Cannot get incremental dump data. Invalid key or `since` is too old, check full resync or try again: %s', $params['url']));
            }

            $json = json_decode($raw);
            $changes = $json->changelog;

            if (sizeof($changes) > 0) {
                $this->mPart = $json->revision;
                $this->perform_incremental_update($okapi, $changes);
            }
            $revision = $json->revision;
            $more = $json->more;
            $this->save_last_update($okapi, $revision);
        }
    }
}
