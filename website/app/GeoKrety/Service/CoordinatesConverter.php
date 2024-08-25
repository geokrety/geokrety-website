<?php

namespace GeoKrety\Service;

use PHPCoord\CoordinateReferenceSystem\Geographic2D;
use PHPCoord\Point\GeographicPoint;
use PHPCoord\Point\UTMPoint;
use PHPCoord\UnitOfMeasure\Angle\Degree;
use PHPCoord\UnitOfMeasure\Length\Metre;

class CoordinatesConverter {
    // N 57° 27.758 E 022° 50.999
    // N 047° 20,363' O 015° 02,705'
    // N52°47.862 O008°.23.786
    // 50.260147°N, 21.970291°E
    // 53°50'16.85"N 23° 6'19.78"E
    // 49°25'59.146"N, 17°28'25.660"E
    // 46° 44′ 0″ N, 12° 11′ 0″ E
    // 55° 3′ 23.96″ N, 9° 44′ 32.71″ E
    // 50°54′N 15°44′E
    // 56.326N 54.235O

    // N 52° 12' 18.74", E 21° 11' 27.21"
    // - 49°49`59.282" E 09°52´21.216"

    // N 52° 12' 18", E 21° 11' 27"

    // 52 12.3123 21 11.45345
    // N52 12.3123 E21 11.45345
    // N 52 12.3123 E 21 11.45345

    // N 52° 10.369' - 021° 01.542'
    // S 52°36.002 E013°19.205 - nie dzialalo, juz dziala
    // N 49°49.59 W 09°52.2 - nie dzialalo, juz dziala

    // 52.205205 21.190891
    // 52.205205/21.190891
    // 52.205205\21.190891
    // N 52.205205 W 21.190891
    // -52.205205 +21.190891

    public static function parse($coords) {
        $ret[0] = '';
        $ret[1] = '';
        $ret['format'] = '';
        $ret['error'] = '';

        if (empty($coords)) {
            $ret['error'] = _('Missing or invalid coordinates.');

            return $ret;
        }

        // 57.462633 22.849983
        $fromDecimalDegree = '/^(?P<angle1>[\+−-]?\d+(\.\d*)?)[\s+,\/\\\](?P<angle2>[\+−-]?\d+(\.\d*)?)$/u';
        $found = preg_match($fromDecimalDegree, $coords, $parts);
        if ($found !== 0) {
            $ret['format'] = 'fromDecimalDegree';
            $ret[0] = sprintf('%.6f', $parts['angle1']);
            $ret[1] = sprintf('%.6f', $parts['angle2']);

            return $ret;
        }

        $regexes = [
            // 56.326N 54.235E
            'fromDegreeHemisphere' => '/^(?P<angle1>\d+\.?\d*([°º][,\.]?)?[NS])(?P<angle2>\d+\.?\d*([°º][,\.]?)?[EWO])$/u',
            // N 56.326 E 54.235
            'fromHemisphereDegree' => '/^(?P<angle1>[NS]\d+\.?\d*[°º]?)(?P<angle2>[EWO]\d+\.?\d*[°º]?)$/u',
            // 50°54′N 15°44′E
            'fromDegreeMinuteHemisphere' => '/^(?P<angle1>\d+([°º][,\.]?)?(\d+\.?\d*[′\'])?[NS])(?P<angle2>\d+([°º][,\.]?)?(\d+\.?\d*[′\'])?[EWO])$/u',
            // 50°54′N 15°44′E
            'fromDegreeMinute' => '/^(?P<angle1>[\+−-]?\d+[°º]?(\d+\.?\d*[′\'])?)(?P<angle2>[−-]?\d+[°º]?(\d+\.?\d*[′\'])?)$/u',
            // 53°50'16.85"N 23° 6'19.78"E
            'fromDegreeMinuteSecondHemisphere' => '/^(?P<angle1>\d+([°º][,\.]?)?(\d+[′\'])?(\d+\.?\d*[″"])?[NS])(?P<angle2>\d+([°º][,\.]?)?(\d+[′\'])?(\d+\.?\d*[″"])?[EWO])$/u',

            // 52° 12' 18.74", -21° 11' 27.21"
            'fromDegreeMinuteSecond' => '/^(?P<angle1>[−-]?\d+([°º][,\.]?)?(\d+[′\'])?(\d+\.?\d*[″"])?)(,)?(?P<angle2>[−-]?\d+([°º][,\.]?)?(\d+[′\'])?(\d+\.?\d*[″"])?)$/u',
            // N 57° 27.758' E 022° 50.999'
            'fromHemisphereDegreeMinute' => '/^(?P<angle1>[NS]\d+([°º][,\.]?)?(\d+[,\.]?\d*[′\'])?)(?P<angle2>[EWO]\d+([°º][,\.]?)?(\d+[,\.]?\d*[′\'])?)$/u',
            // # N 57° 27.758' E 022° 50.999'
            // 'fromHemisphereDegreeMinuteGC' => '/^(?P<angle1>[NS]\d+([°º][,\.]?)?(\d+[,\.]?\d*)?)(?P<angle2>[EWO]\d+([°º][,\.]?)?(\d+[,\.]?\d*)?)$/u',
            // N55° 3′ 23.96″ E9° 44′ 32.71″
            'fromHemisphereDegreeMinuteSecond' => '/^(?P<angle1>[NS]\d+([°º][,\.]?)?(\d+[′\'])?(\d+\.?\d*[″"])?)(?P<angle2>[EWO]\d+([°º][,\.]?)?(\d+[′\'])?(\d+\.?\d*[″"])?)$/u',

            // 32T E 327620 N 4840069
            'UTM' => '/^(?P<zone>\d{2})(?P<hemisphere>[A-Z])E(?P<dist1>\d+)N(?P<dist2>\d+)$/u',
        ];

        // 52 12.3123 -21 11.45345 -> 52° 12.3123' -21° 11.45345'
        $coords = preg_replace_callback(
            '/^(?P<negative1>[NS\+−-])?\s*(?P<degrees1>\d+)([°º][,\.]?\s*|\s+|[,\/\\\])(?P<arcminutes1>\d+\.?\d*)[`´′\']?\s+(?P<negative2>[EWO\+−-])?\s*(?P<degrees2>\d+)([°º][,\.]?\s*|\s+|[,\/\\\])(?P<arcminutes2>\d+\.?\d*)[`´′\']?$/u',
            function ($m) {
                $m['negative1'] = in_array($m['negative1'], ['-', 'S']) ? 'S' : 'N';
                $m['negative2'] = in_array($m['negative2'], ['-', 'W', 'O']) ? 'W' : 'E';

                return "{$m['negative1']}{$m['degrees1']}°{$m['arcminutes1']}'{$m['negative2']}{$m['degrees2']}°{$m['arcminutes2']}'";
            }, $coords);

        // 52 12 18.74 -21 11 27.21 -> 52° 12' 18.74" -21° 11' 27.21"
        $coords = preg_replace_callback(
            '/^(?P<negative1>[NS\+−-])?\s*(?P<degrees1>\d+)([°º][,\.]?\s*|\s+|[,\/\\\])(?P<arcminutes1>\d+)([`´′\']?\s*|s+)(?P<arcseconds1>\d+\.?\d*)[″"]?\s+(?P<negative2>[EWO\+−-])?\s*(?P<degrees2>\d+)([°º][,\.]?\s*|\s+|[,\/\\\])(?P<arcminutes2>\d+)([`´′\']?\s*|s+)(?P<arcseconds2>\d+\.?\d*)[″"]?$/u',
            function ($m) {
                $m['negative1'] = in_array($m['negative1'], ['-', 'S']) ? 'S' : 'N';
                $m['negative2'] = in_array($m['negative2'], ['-', 'W', 'O']) ? 'W' : 'E';

                return "{$m['negative1']}{$m['degrees1']}°{$m['arcminutes1']}'{$m['arcseconds1']}\"{$m['negative2']}{$m['degrees2']}°{$m['arcminutes2']}'{$m['arcseconds2']}\"";
            }, $coords);

        $input = str_replace(' ', '', $coords);
        $input = str_replace('O', 'W', $input);
        $input = str_replace('+', '', $input);
        $input = preg_replace('/[″"],/', '"', $input);
        $input = str_replace(',', '.', $input);
        $input = preg_replace('/([°º])[,\.]/', '${1}', $input);
        $input = preg_replace('/([NSEWO])[,\.]/', '${1}', $input);

        $crs = Geographic2D::fromSRID(Geographic2D::EPSG_WGS_84);

        $matched_format = null;
        foreach ($regexes as $format => $regex) {
            $found = preg_match($regex, $input, $parts);
            if ($found !== 0) {
                $matched_format = $ret['format'] = $format;
                break;
            }
        }

        if ($matched_format === null) {
            $ret['error'] = _('Bad coordinates or unknown format.');

            return $ret;
        }

        if ($matched_format === 'UTM') {
            $utm = new UTMPoint(
                $crs,
                new Metre($parts['dist1']),
                new Metre($parts['dist2']),
                intval($parts['zone']),
                $parts['hemisphere'] < 'N' ? UTMPoint::HEMISPHERE_SOUTH : UTMPoint::HEMISPHERE_NORTH,
            );
            $point = $utm->asGeographicPoint();
        } else {
            if ($ret['format'] == 'fromHemisphereDegreeMinuteGC') {
                $matched_format = 'fromHemisphereDegreeMinute';
                foreach (['angle1', 'angle2'] as $part) {
                    if (substr($parts[$part], -1) != '\'') {
                        $parts[$part] .= '\'';
                    }
                }
            }
            $point = GeographicPoint::create(
                $crs,
                Degree::$matched_format($parts['angle1']),
                Degree::$matched_format($parts['angle2']),
                null
            );
        }

        $ret[0] = sprintf('%.6f', $point->getLatitude()->getValue());
        $ret[1] = sprintf('%.6f', $point->getLongitude()->getValue());

        return $ret;
    }
}
