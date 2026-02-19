<?php

namespace GeoKrety\Service;

use GeoKrety\Email\BasePHPMailer;
use GeoKrety\Model\User;

class StaticMapImage {
    private const HOME_ICON_SIZE = 48;
    private const GK_ICON_SIZE = 16;

    /**
     * Generate and embed a static map image showing GeoKrets dropped near user's home.
     *
     * @param BasePHPMailer $email     Email instance to embed image in
     * @param User          $user      User object with home coordinates
     * @param array         $positions Array of position geometries (from query result)
     * @param string        $imageId   Content ID for embedding (default: GK_NEAR_HOME_IMG)
     *
     * @return bool true if image was successfully generated and embedded, false otherwise
     *
     * @throws \Exception If file download or image handling fails
     */
    public static function generateHomeMapWithMarkers(
        BasePHPMailer $email,
        User $user,
        array $positions,
        string $imageId = 'GK_NEAR_HOME_IMG',
    ): bool {
        if (!$user->hasHomeCoordinates()) {
            return false;
        }

        try {
            // Build GeoJSON for the map
            $geojson = self::buildGeoJSON($positions, $user);

            // Build URL parameters for static maps service
            $imgUrlParams = http_build_query([
                'arrows' => true,
                'geojson' => json_encode($geojson),
                'width' => 640,
                'height' => 480,
                'oxipng' => true,
                'maxZoom' => 13,
                'markerIconOptions' => sprintf(
                    '{"iconUrl": "%s/pins/green.png", iconAnchor: [6, 20]}',
                    GK_CDN_ICONS_URL
                ),
            ]);

            $mapUrl = sprintf('%s?%s', GK_OSM_STATIC_MAPS_URI, $imgUrlParams);

            // Download the image
            $fp = fopen('php://memory', 'w');
            File::download($mapUrl, $fp);
            rewind($fp);
            $imgString = stream_get_contents($fp);
            fclose($fp);

            // Embed image in email
            $email->addStringEmbeddedImage($imgString, $imageId, 'gk_near_home.png');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Build GeoJSON FeatureCollection from positions with home location marker.
     *
     * @param array $queryResult Array from database query containing 'geojson' field
     * @param User  $user        User object with home coordinates
     *
     * @return object GeoJSON FeatureCollection
     */
    private static function buildGeoJSON(array $queryResult, User $user): object {
        // Build home marker feature
        $homeFeature = self::buildHomeMarkerFeature($user);

        // Extract GeoJSON from query result
        if (!empty($queryResult) && isset($queryResult[0]['geojson'])) {
            $geojson = json_decode($queryResult[0]['geojson']);
        } else {
            $geojson = (object) [
                'type' => 'FeatureCollection',
                'features' => [],
            ];
        }

        // Ensure features array exists
        if (!isset($geojson->features) || !is_array($geojson->features)) {
            $geojson->features = [];
        }

        // Add home marker at the beginning
        array_unshift($geojson->features, $homeFeature);

        return $geojson;
    }

    /**
     * Build a GeoJSON feature for home location marker.
     *
     * @param User $user User with home coordinates
     *
     * @return array GeoJSON Feature object
     */
    private static function buildHomeMarkerFeature(User $user): array {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $user->home_longitude, (float) $user->home_latitude],
            ],
            'markerIconOptions' => [
                'iconUrl' => GK_CDN_ICONS_URL.'/home'.self::HOME_ICON_SIZE.'.png',
                'iconAnchor' => [24, 24],
            ],
        ];
    }
}
