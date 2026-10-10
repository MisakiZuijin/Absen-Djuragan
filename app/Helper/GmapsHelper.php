<?php

namespace App\Helper;

use Illuminate\Support\Facades\Log;

class GmapsHelper
{
    /**
     * Resolves coordinates and address from any input:
     * - Direct coordinate string (e.g. "-7.79579, 110.40823")
     * - Shortlink Google Maps (e.g. "https://maps.app.goo.gl/...")
     * - Full Google Maps URL (e.g. "https://www.google.com/maps/place/...")
     * - Address / place name (via geocoding fallback)
     *
     * @param string $input
     * @return array{lat: float, lng: float, address: ?string, source: string, display_name?: string}|null
     */
    public static function resolveCoordinates(string $input): ?array
    {
        $input = trim($input);
        if (empty($input)) {
            return null;
        }

        // 1. Direct coordinate string: "-7.79579, 110.40823" or "-7,79579, 110,40823"
        if (preg_match('/^(-?\d+[.,]\d+)\s*,\s*(-?\d+[.,]\d+)$/', $input, $m)) {
            $lat = (float) str_replace(',', '.', $m[1]);
            $lng = (float) str_replace(',', '.', $m[2]);
            if (self::isValidCoordinate($lat, $lng)) {
                $address = self::reverseGeocode($lat, $lng);
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $address,
                    'source' => 'direct_coordinate',
                    'display_name' => $address ?: "Koordinat: {$lat}, {$lng}"
                ];
            }
        }

        // 2. Contains place pin (!3d... !4d...)
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $input, $m)) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
            if (self::isValidCoordinate($lat, $lng)) {
                $address = self::extractPlaceNameFromText($input) ?: self::reverseGeocode($lat, $lng);
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $address,
                    'source' => 'direct_place',
                    'display_name' => $address ?: $input
                ];
            }
        }

        // 3. Contains @lat,lng
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $input, $m)) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
            if (self::isValidCoordinate($lat, $lng)) {
                $address = self::extractPlaceNameFromText($input) ?: self::reverseGeocode($lat, $lng);
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $address,
                    'source' => 'direct_at',
                    'display_name' => $address ?: $input
                ];
            }
        }

        // 4. Contains query parameter ?q=lat,lng, ll=lat,lng, or daddr=lat,lng
        if (preg_match('/[?&](?:q|query|ll|daddr)=(-?\d+\.\d+),\s*(?:\+|%20)?(-?\d+\.\d+)/', $input, $m)) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
            if (self::isValidCoordinate($lat, $lng)) {
                $address = self::extractPlaceNameFromText($input) ?: self::reverseGeocode($lat, $lng);
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $address,
                    'source' => 'direct_query',
                    'display_name' => $address ?: $input
                ];
            }
        }

        // 5. Contains dropped pin / coordinate in path: /maps/search/lat,+lng or /maps/place/lat,+lng
        if (preg_match('#/maps/(?:search|place)/(-?\d+\.\d+),\s*(?:\+|%20)?(-?\d+\.\d+)#i', $input, $m)) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
            if (self::isValidCoordinate($lat, $lng)) {
                $address = self::extractPlaceNameFromText($input) ?: self::reverseGeocode($lat, $lng);
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $address,
                    'source' => 'direct_path_pin',
                    'display_name' => $address ?: "Koordinat: {$lat}, {$lng}"
                ];
            }
        }

        // 6. Input is URL or shortlink
        if (filter_var($input, FILTER_VALIDATE_URL) || preg_match('/^(https?:\/\/|maps\.app\.goo\.gl|goo\.gl|www\.google\.[a-z.]+\/maps)/i', $input)) {
            $url = $input;
            if (!preg_match('/^https?:\/\//i', $url)) {
                $url = 'https://' . $url;
            }

            $resolvedFromUrl = self::resolveUrl($url);
            if ($resolvedFromUrl) {
                return $resolvedFromUrl;
            }
        }

        // 7. Fallback: Geocode text address
        return self::geocodeAddress($input);
    }

    /**
     * Resolves coordinates and address from a Google Maps URL by following redirects and scraping data.
     *
     * @param string $url
     * @return array{lat: float, lng: float, address: ?string, source: string, display_name?: string}|null
     */
    public static function resolveUrl(string $url): ?array
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 7);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            if (PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            $lat = null;
            $lng = null;
            $source = null;

            // Prioritas 1: Pin marker tempat resmi Google (!3dlat!4dlng)
            if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $effectiveUrl, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];
                $source = 'gmaps_place';
            }
            // Prioritas 2: Dropped pin / Search pin koordinat di URL path (/maps/search/lat,+lng atau /maps/place/lat,+lng)
            elseif (preg_match('#/maps/(?:search|place)/(-?\d+\.\d+),\s*(?:\+|%20)?(-?\d+\.\d+)#i', $effectiveUrl, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];
                $source = 'gmaps_search_pin';
            }
            // Prioritas 3: Center kamera URL (@lat,lng)
            elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $effectiveUrl, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];
                $source = 'gmaps_camera';
            }
            // Prioritas 4: Query parameter (?q=lat,lng atau ?ll=lat,lng atau ?daddr=lat,lng)
            elseif (preg_match('/[?&](?:q|query|ll|daddr)=(-?\d+\.\d+),\s*(?:\+|%20)?(-?\d+\.\d+)/', $effectiveUrl, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];
                $source = 'gmaps_query';
            }
            // Prioritas 5: Cek staticmap/meta pada HTML body
            elseif (!empty($response)) {
                if (preg_match('/center=(-?\d+\.\d+)%2C(-?\d+\.\d+)/i', $response, $m)) {
                    $lat = (float) $m[1];
                    $lng = (float) $m[2];
                    $source = 'gmaps_staticmap';
                } elseif (preg_match('/itemprop="image"\s+content=".*?center=(-?\d+\.\d+)%2C(-?\d+\.\d+)/i', $response, $m)) {
                    $lat = (float) $m[1];
                    $lng = (float) $m[2];
                    $source = 'gmaps_itemprop';
                } elseif (preg_match('/\[null,null,(-?\d{1,2}\.\d{4,}),(-?\d{1,3}\.\d{4,})\]/', $response, $m)) {
                    $lat = (float) $m[1];
                    $lng = (float) $m[2];
                    $source = 'gmaps_json_pin';
                }
            }

            // Prioritas 6: Cek header redirect Location jika URL akhir belum menghasilkan koordinat
            if (($lat === null || $lng === null) && !empty($response)) {
                if (preg_match_all('/^location:\s*(.*?)$/im', $response, $locMatches)) {
                    foreach ($locMatches[1] as $loc) {
                        $loc = trim($loc);
                        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $loc, $m)) {
                            $lat = (float) $m[1];
                            $lng = (float) $m[2];
                            $source = 'gmaps_redirect_header';
                            $effectiveUrl = $loc;
                            break;
                        } elseif (preg_match('#/maps/(?:search|place)/(-?\d+\.\d+),\s*(?:\+|%20)?(-?\d+\.\d+)#i', $loc, $m)) {
                            $lat = (float) $m[1];
                            $lng = (float) $m[2];
                            $source = 'gmaps_redirect_header';
                            $effectiveUrl = $loc;
                            break;
                        } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $loc, $m)) {
                            $lat = (float) $m[1];
                            $lng = (float) $m[2];
                            $source = 'gmaps_redirect_header';
                            $effectiveUrl = $loc;
                            break;
                        } elseif (preg_match('/[?&](?:q|query|ll|daddr)=(-?\d+\.\d+),\s*(?:\+|%20)?(-?\d+\.\d+)/', $loc, $m)) {
                            $lat = (float) $m[1];
                            $lng = (float) $m[2];
                            $source = 'gmaps_redirect_header';
                            $effectiveUrl = $loc;
                            break;
                        }
                    }
                }
            }

            if ($lat !== null && $lng !== null && self::isValidCoordinate($lat, $lng)) {
                // Ambil alamat dari URL atau HTML Google Maps
                $address = self::extractAddressFromGmaps($effectiveUrl, $response);
                if (empty($address)) {
                    $address = self::reverseGeocode($lat, $lng);
                }

                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $address,
                    'source' => $source,
                    'display_name' => $address ?: $effectiveUrl
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('GmapsHelper::resolveUrl failed: ' . $e->getMessage(), ['url' => $url]);
        }

        return null;
    }

    /**
     * Extract human-readable address or place name from Google Maps URL or HTML response
     */
    public static function extractAddressFromGmaps(string $effectiveUrl, ?string $html = null): ?string
    {
        try {
            // 1. Ekstrak dari path /maps/place/
            if (preg_match('#/maps/place/([^/@?]+)#i', $effectiveUrl, $m)) {
                $raw = urldecode(str_replace('+', ' ', $m[1]));
                $raw = trim($raw);
                if (!empty($raw) && !preg_match('/^-?\d+[.,]\d+/', $raw)) {
                    return $raw;
                }
            }

            // 2. Ekstrak dari HTML title atau meta
            if (!empty($html)) {
                if (preg_match('/<title>(.*?)(?: - Google Maps)?<\/title>/i', $html, $tm)) {
                    $clean = trim(html_entity_decode($tm[1], ENT_QUOTES, 'UTF-8'));
                    $clean = preg_replace('/\s*-\s*Google Maps\s*$/i', '', $clean);
                    if (!empty($clean) && stripos($clean, 'Google Maps') === false && !preg_match('/^-?\d+[.,]\d+/', $clean)) {
                        return $clean;
                    }
                }
                if (preg_match('/<meta property="og:title" content="(.*?)"/i', $html, $om)) {
                    $clean = trim(html_entity_decode($om[1], ENT_QUOTES, 'UTF-8'));
                    $clean = preg_replace('/\s*-\s*Google Maps\s*$/i', '', $clean);
                    if (!empty($clean) && stripos($clean, 'Google Maps') === false && !preg_match('/^-?\d+[.,]\d+/', $clean)) {
                        return $clean;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GmapsHelper::extractAddressFromGmaps failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Extracts place name from text query if present
     */
    private static function extractPlaceNameFromText(string $text): ?string
    {
        try {
            if (preg_match('#/maps/place/([^/@?]+)#i', $text, $m)) {
                $raw = urldecode(str_replace('+', ' ', $m[1]));
                $raw = trim($raw);
                if (!empty($raw) && !preg_match('/^-?\d+[.,]\d+/', $raw)) {
                    return $raw;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return null;
    }

    /**
     * Reverse geocodes coordinates to a full formatted address using Photon and OSM Nominatim.
     */
    public static function reverseGeocode(float $lat, float $lng): ?string
    {
        // 1. OSM Nominatim Reverse Geocoding (paling lengkap untuk Indonesia: jalan, kelurahan, kecamatan, kabupaten, provinsi)
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Absen-Djuragan/1.0; contact@absen-djuragan.local)');
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
            if (PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            if (!empty($res)) {
                $data = json_decode($res, true);
                if (!empty($data['display_name'])) {
                    return $data['display_name'];
                }
            }
        } catch (\Throwable $e) {
            // Lanjut ke fallback
        }

        // 2. Photon Komoot Reverse (Cepat)
        try {
            $url = "https://photon.komoot.io/reverse?lat={$lat}&lon={$lng}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Absen-Djuragan)');
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
            if (PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            if (!empty($res)) {
                $data = json_decode($res, true);
                if (!empty($data['features'][0]['properties'])) {
                    $p = $data['features'][0]['properties'];
                    $parts = array_filter([
                        $p['name'] ?? null,
                        $p['street'] ?? null,
                        $p['housenumber'] ?? null,
                        $p['district'] ?? null,
                        $p['city'] ?? $p['county'] ?? null,
                        $p['state'] ?? null,
                        $p['postcode'] ?? null,
                    ]);
                    if (!empty($parts)) {
                        return implode(', ', array_unique($parts));
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan
        }

        return null;
    }

    /**
     * Geocodes an address via Google Search (tbm=map), Photon Komoot, and OSM Nominatim.
     *
     * @param string $query
     * @return array{lat: float, lng: float, address: ?string, source: string, display_name: string}|null
     */
    public static function geocodeAddress(string $query): ?array
    {
        $query = trim($query);
        if (empty($query)) {
            return null;
        }

        // 1. Google Maps Search scraping
        try {
            $searchUrl = 'https://www.google.com/search?tbm=map&authuser=0&hl=id&gl=id&q=' . urlencode($query);
            $ch = curl_init($searchUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 9);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            ]);

            $html = (string) curl_exec($ch);
            $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            if (PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            $lat = null;
            $lng = null;
            $source = null;

            if (preg_match('/!3d(-?\d{1,2}\.\d{4,})!4d(-?\d{1,3}\.\d{4,})/', $effectiveUrl . ' ' . $html, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];
                $source = 'gmaps_place_pin';
            } elseif (preg_match('/\[null,null,(-?\d{1,2}\.\d{4,}),(-?\d{1,3}\.\d{4,})\]/', $html, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];
                $source = 'gmaps_json_pin';
            } elseif (preg_match('/@(-?\d{1,2}\.\d{4,}),(-?\d{1,3}\.\d{4,})/', $effectiveUrl . ' ' . $html, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];
                $source = 'gmaps_camera';
            } elseif (preg_match('/center=(-?\d{1,2}\.\d{4,})%2C(-?\d{1,3}\.\d{4,})/i', $html, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];
                $source = 'gmaps_staticmap';
            }

            if ($lat !== null && $lng !== null && self::isValidCoordinate($lat, $lng)) {
                $displayName = $query;
                if (preg_match('/<title>(.*?)(?: - Google Maps)?<\/title>/i', $html, $tm)) {
                    $cleanTitle = trim(html_entity_decode($tm[1], ENT_QUOTES, 'UTF-8'));
                    if (!empty($cleanTitle) && stripos($cleanTitle, 'Google Maps') === false) {
                        $displayName = $cleanTitle;
                    }
                }
                $address = self::extractAddressFromGmaps($effectiveUrl, $html) ?: self::reverseGeocode($lat, $lng) ?: $displayName;
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $address,
                    'source' => $source,
                    'display_name' => $displayName
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('GmapsHelper::geocodeAddress (Google) failed: ' . $e->getMessage());
        }

        // 2. Fallback: Photon Komoot
        try {
            $photonUrl = 'https://photon.komoot.io/api/?q=' . urlencode($query) . '&limit=3';
            $ch = curl_init($photonUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Absen-Djuragan)');
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $photonRes = curl_exec($ch);
            if (PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            if (!empty($photonRes)) {
                $photonData = json_decode($photonRes, true);
                if (!empty($photonData['features'])) {
                    $f = $photonData['features'][0];
                    $coords = $f['geometry']['coordinates'] ?? null;
                    if ($coords && count($coords) >= 2) {
                        $lat = (float) $coords[1];
                        $lng = (float) $coords[0];
                        if (self::isValidCoordinate($lat, $lng)) {
                            $prop = $f['properties'] ?? [];
                            $label = $prop['name'] ?? $query;
                            return [
                                'lat' => $lat,
                                'lng' => $lng,
                                'address' => $label,
                                'source' => 'photon_komoot',
                                'display_name' => $label
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GmapsHelper::geocodeAddress (Photon) failed: ' . $e->getMessage());
        }

        // 3. Fallback: OSM Nominatim
        try {
            $nominatimUrl = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($query) . '&limit=1';
            $ch = curl_init($nominatimUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Absen-Djuragan/1.0; contact@absen-djuragan.local)');
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $nomRes = curl_exec($ch);
            if (PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            if (!empty($nomRes)) {
                $nomData = json_decode($nomRes, true);
                if (!empty($nomData) && isset($nomData[0]['lat'], $nomData[0]['lon'])) {
                    $lat = (float) $nomData[0]['lat'];
                    $lng = (float) $nomData[0]['lon'];
                    if (self::isValidCoordinate($lat, $lng)) {
                        $label = $nomData[0]['display_name'] ?? $query;
                        return [
                            'lat' => $lat,
                            'lng' => $lng,
                            'address' => $label,
                            'source' => 'nominatim',
                            'display_name' => $label
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GmapsHelper::geocodeAddress (Nominatim) failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Helper to validate reasonable coordinate range
     */
    public static function isValidCoordinate(?float $lat, ?float $lng): bool
    {
        if ($lat === null || $lng === null) {
            return false;
        }
        return ($lat >= -90.0 && $lat <= 90.0) && ($lng >= -180.0 && $lng <= 180.0) && ($lat != 0.0 || $lng != 0.0);
    }
}
