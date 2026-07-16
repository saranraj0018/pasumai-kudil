<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoNameResolver
{
    public static function centroid(array $coordinates): array
    {
        $latSum = 0;
        $lngSum = 0;
        $count = count($coordinates);

        foreach ($coordinates as $point) {
            $latSum += (float) $point['lat'];
            $lngSum += (float) $point['lng'];
        }

        return [
            'lat' => $latSum / $count,
            'lng' => $lngSum / $count,
        ];
    }

    public static function resolveName(float $lat, float $lng): ?string
    {
        $apiKey = config('services.google_maps.api_key');

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => "{$lat},{$lng}",
            'key'    => $apiKey,
        ]);
         if (!$response->successful()) {
            return null;
        }

        $results = $response->json('results');
        if (empty($results)) {
            return null;
        }

        foreach ($results as $result) {
            foreach ($result['address_components'] as $comp) {
                if (in_array('sublocality', $comp['types']) || in_array('locality', $comp['types'])) {
                    return $comp['long_name'];
                }
            }
        }

        return $results[0]['formatted_address'] ?? null;
    }

    // public static function resolveAllNamesInPolygon(array $polygon, float $spacingKm = 5.0): array
    // {
    //     $points = self::samplePointsInPolygon($polygon, $spacingKm);

    //     // Safety net: if grid spacing was too coarse for this polygon's size,
    //     // at least always test the centroid so we never return empty.
    //     if (empty($points)) {
    //         $points[] = self::centroid($polygon);
    //     }

    //     $names = [];

    //     foreach ($points as $point) {
    //         $name = self::resolveName($point['lat'], $point['lng']);
    //         if ($name) {
    //             $names[$name] = true;
    //         }
    //         usleep(200000);
    //     }

    //     return array_keys($names);
    // }

    public static function resolveAllNamesInPolygon(array $polygon, float $spacingKm = 5.0): array
    {
        $points = self::samplePointsInPolygon($polygon, $spacingKm);

        if (empty($points)) {
            $points[] = self::centroid($polygon);
        }

        $names = [];

        foreach ($points as $point) {
            $name = self::resolveName($point['lat'], $point['lng']);
            if ($name && !isset($names[$name])) {
                $names[$name] = [
                    'name' => $name,
                    'lat'  => $point['lat'],
                    'lng'  => $point['lng'],
                ];
            }
            usleep(200000);
        }

        return array_values($names);
    }

    /**
     * Generate a grid of lat/lng points that fall inside the polygon.
     */
    public static function samplePointsInPolygon(array $polygon, float $spacingKm = 5.0): array
    {
        $lats = array_map('floatval', array_column($polygon, 'lat'));
        $lngs = array_map('floatval', array_column($polygon, 'lng'));

        $minLat = min($lats);
        $maxLat = max($lats);
        $minLng = min($lngs);
        $maxLng = max($lngs);

        $avgLat = ($minLat + $maxLat) / 2;
        $latStep = $spacingKm / 111.0;
        $lngStep = $spacingKm / (111.0 * cos(deg2rad($avgLat)));

        $points = [];

        for ($lat = $minLat; $lat <= $maxLat; $lat += $latStep) {
            for ($lng = $minLng; $lng <= $maxLng; $lng += $lngStep) {
                if (self::isPointInPolygon($lat, $lng, $polygon)) {
                    $points[] = ['lat' => $lat, 'lng' => $lng];
                }
            }
        }

        return $points;
    }

    private static function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $numPoints = count($polygon);
        $j = $numPoints - 1;

        for ($i = 0; $i < $numPoints; $i++) {
            $latI = (float) $polygon[$i]['lat'];
            $lngI = (float) $polygon[$i]['lng'];
            $latJ = (float) $polygon[$j]['lat'];
            $lngJ = (float) $polygon[$j]['lng'];

            $intersect = (($lngI > $lng) != ($lngJ > $lng)) &&
                ($lat < ($latJ - $latI) * ($lng - $lngI) / (($lngJ - $lngI) ?: 1e-9) + $latI);

            if ($intersect) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }
    public static function autoSpacingKm(array $polygon): float
    {
        $lats = array_map('floatval', array_column($polygon, 'lat'));
        $lngs = array_map('floatval', array_column($polygon, 'lng'));

        $diagonalKm = sqrt(
            pow((max($lats) - min($lats)) * 111, 2) +
                pow((max($lngs) - min($lngs)) * 111 * cos(deg2rad(array_sum($lats) / count($lats))), 2)
        );

        return match (true) {
            $diagonalKm > 80 => 15.0,
            $diagonalKm > 30 => 8.0,
            $diagonalKm > 10 => 3.0,
            default          => 1.0,
        };
    }
}
