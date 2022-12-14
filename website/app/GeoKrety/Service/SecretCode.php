<?php

namespace GeoKrety\Service;

use GeoKrety\Model\Geokret;

class SecretCode {
    public const EXCLUDED_PREFIXES = ['GK', ...WaypointInfo::PREFIX_GC, ...WaypointInfo::PREFIX_OC];
    public array $generated_in_transaction = [];

    public static function generateSecId(int $len = GK_SITE_SECID_CODE_LENGTH): string {
        return self::generate(
            GK_GENERATOR_SECID_ALPHABET,
            $len,
        );
    }

    /**
     * @param string      $alphabet The character to use during generation
     * @param int         $len      The generated code length
     * @param string|null $prefix   A custom static prefix
     * @param string|null $suffix   A custom static suffix
     *
     * @return string The tracking code or false if something goes wrong
     *
     * @throws \Exception
     */
    public function generateTrackingCode(string $alphabet = GK_GENERATOR_TRACKING_CODE_ALPHABET, int $len = GK_SITE_TRACKING_CODE_MIN_LENGTH, ?string $prefix = null, ?string $suffix = null): string {
        $i = 0;
        $geokret = new Geokret();
        do {
            if (++$i === GK_TRACKING_CODE_GENERATE_MAX_TRIES) {
                throw new \Exception(_('Failed to generate the Tracking Code'));
            }
            $tracking_code = self::generate(
                strtoupper($alphabet),
                $len,
                strtoupper($prefix),
                strtoupper($suffix),
            );
        } while (
            in_array(strtoupper(substr($tracking_code, 0, 2)), self::EXCLUDED_PREFIXES) or (
                $geokret->findone(['tracking_code = ?', $tracking_code]) !== false
            )
        );
        $this->generated_in_transaction[] = $tracking_code;

        return $tracking_code;
    }

    public static function generate(string $alphabet, int $len = GK_SITE_TRACKING_CODE_MIN_LENGTH, ?string $prefix = null, ?string $suffix = null): string {
        $seed = str_split(str_repeat($alphabet, 42));
        shuffle($seed);

        $rand = [];
        if (strlen($prefix) + strlen($suffix) < $len) {
            $len_ = $len - strlen($prefix) - strlen($suffix);
            if ($len_ === 1) {
                $rand[] = $seed[array_rand($seed, $len_)];
            } else {
                foreach (array_rand($seed, $len_) as $k) {
                    $rand[] = $seed[$k];
                }
            }
        }

        if (!is_null($prefix)) {
            array_unshift($rand, $prefix);
        }
        if (!is_null($suffix)) {
            array_push($rand, $suffix);
        }

        return join('', $rand);
    }
}
