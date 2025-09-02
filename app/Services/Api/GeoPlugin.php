<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Api;

use App\Models\City;
use App\Services\Requests\HttpRequest;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\Container as App;
use Illuminate\Support\Facades\Log;

/**
 * Class GeoPlugin
 * Enhanced IP geolocation service with multiple providers and fallback mechanisms
 */
class GeoPlugin
{
    /**
     * Multiple geolocation service providers with fallback support
     *
     * @var array
     */
    private $providers = [
        'geoplugin' => 'http://www.geoplugin.net/php.gp?ip={IP}&lang={LANG}',
        'ipapi' => 'http://ip-api.com/php/{IP}?fields=status,message,country,countryCode,region,regionName,city,lat,lon,timezone,query',
        'ipstack' => 'http://api.ipstack.com/{IP}?access_key={ACCESS_KEY}',
        'ipinfo' => 'http://ipinfo.io/{IP}/json',
    ];

    /**
     * Default language.
     * supported languages: de, en, es, fr, ja, pt-BR, ru, zh-CN
     *
     * @var string
     */
    private $lang = 'en';

    /**
     * Current provider being used
     *
     * @var string
     */
    public $currentProvider = 'geoplugin';

    /**
     * @var null
     */
    public $ip = null;

    /**
     * @var null
     */
    public $city = null;

    /**
     * @var null
     */
    public $region = null;

    /**
     * @var null
     */
    public $regionCode = null;

    /**
     * @var null
     */
    public $regionName = null;

    /**
     * @var null
     */
    public $dmaCode = null;

    /**
     * @var null
     */
    public $countryCode = null;

    /**
     * @var null
     */
    public $countryName = null;

    /**
     * @var null
     */
    public $inEU = null;

    /**
     * @var null
     */
    public $euVATrate = false;

    /**
     * @var null
     */
    public $continentCode = null;

    /**
     * @var null
     */
    public $continentName = null;

    /**
     * @var null
     */
    public $latitude = null;

    /**
     * @var null
     */
    public $longitude = null;

    /**
     * @var null
     */
    public $locationAccuracyRadius = null;

    /**
     * @var null
     */
    public $timezone = null;

    /**
     * @var null
     */
    public $currencyCode = null;

    /**
     * @var null
     */
    public $currencySymbol = null;

    /**
     * @var null
     */
    public $currencyConverter = null;

    /**
     * GeoHelper constructor.
     */
    public function __construct(protected HttpRequest $request, protected App $app, protected City $cityModel) {}

    /**
     * Locate ip with enhanced detection and fallback mechanisms.
     * If not production environment, return random city.
     *
     * @param  null  $ip
     */
    public function locate($ip = null): bool
    {
        if ($this->app->environment() !== 'production') {
            $data = $this->cityModel->inRandomOrder()->first();
            $this->ip = '0.0.0.0';
            $this->city = $data->city;
            $this->latitude = $data->latitude;
            $this->longitude = $data->longitude;

            return true;
        }

        // Enhanced IP detection with comprehensive fallback
        if (is_null($ip)) {
            $ip = $this->detectRealIP();
        }

        // Validate IP address
        if (! $this->isValidIP($ip)) {
            Log::warning('GeoPlugin: Invalid IP detected', ['ip' => $ip]);
            $this->setFallbackLocation();

            return false;
        }

        // Try each provider until one succeeds
        foreach ($this->providers as $providerName => $providerUrl) {
            $this->currentProvider = $providerName;
            Log::info("GeoPlugin: Attempting geolocation with {$providerName}", ['ip' => $ip]);

            if ($this->tryProvider($providerUrl, $ip)) {
                Log::info("GeoPlugin: Successfully located IP with {$providerName}", [
                    'ip' => $ip,
                    'city' => $this->city,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ]);

                return true;
            }
        }

        // All providers failed, use fallback
        Log::error('GeoPlugin: All geolocation providers failed', ['ip' => $ip]);
        $this->setFallbackLocation();

        return false;
    }

    /**
     * Enhanced IP detection with comprehensive server variable checking
     */
    private function detectRealIP(): string
    {
        $ipSources = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'HTTP_X_REAL_IP',            // Nginx proxy
            'REMOTE_ADDR',                // Standard
        ];

        foreach ($ipSources as $source) {
            if (! empty($_SERVER[$source])) {
                $ips = $_SERVER[$source];

                // Handle comma-separated IPs (common with X-Forwarded-For)
                if (str_contains($ips, ',')) {
                    $ipList = array_map('trim', explode(',', $ips));
                    // Get the first non-private IP
                    foreach ($ipList as $ip) {
                        if ($this->isValidIP($ip) && ! $this->isPrivateIP($ip)) {
                            return $ip;
                        }
                    }
                    // If no public IP found, use the first valid one
                    foreach ($ipList as $ip) {
                        if ($this->isValidIP($ip)) {
                            return $ip;
                        }
                    }
                } else {
                    // Single IP
                    $ip = trim($ips);
                    if ($this->isValidIP($ip)) {
                        return $ip;
                    }
                }
            }
        }

        // Fallback to localhost if no valid IP found
        return '127.0.0.1';
    }

    /**
     * Validate IP address format
     */
    private function isValidIP(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Check if IP is private/local
     */
    private function isPrivateIP(string $ip): bool
    {
        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Try a specific geolocation provider
     */
    private function tryProvider(string $providerUrl, string $ip): bool
    {
        try {
            $url = $this->buildProviderUrl($providerUrl, $ip);
            if (! $url) {
                return false;
            }

            $response = $this->fetch($url);
            if (! $response) {
                return false;
            }

            return $this->parseProviderResponse($response);
        } catch (Exception $e) {
            Log::warning("GeoPlugin: Provider {$this->currentProvider} failed", [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);

            return false;
        }
    }

    /**
     * Build URL for specific provider
     */
    private function buildProviderUrl(string $providerUrl, string $ip): ?string
    {
        $url = str_replace('{IP}', $ip, $providerUrl);
        $url = str_replace('{LANG}', $this->lang, $url);

        // Handle API keys if needed
        if (str_contains($url, '{ACCESS_KEY}')) {
            $apiKey = config('services.ipstack.key');
            if (! $apiKey) {
                Log::warning('GeoPlugin: IPStack API key not configured');

                return null;
            }
            $url = str_replace('{ACCESS_KEY}', $apiKey, $url);
        }

        return $url;
    }

    /**
     * Parse response based on current provider
     */
    private function parseProviderResponse($response): bool
    {
        try {
            switch ($this->currentProvider) {
                case 'geoplugin':
                    return $this->parseGeoPluginResponse($response);
                case 'ipapi':
                    return $this->parseIPAPIResponse($response);
                case 'ipstack':
                    return $this->parseIPStackResponse($response);
                case 'ipinfo':
                    return $this->parseIPInfoResponse($response);
                default:
                    return false;
            }
        } catch (Exception $e) {
            Log::error("GeoPlugin: Failed to parse {$this->currentProvider} response", [
                'error' => $e->getMessage(),
                'response' => $response,
            ]);

            return false;
        }
    }

    /**
     * Set fallback location (Tallahassee, FL)
     */
    private function setFallbackLocation(): void
    {
        $this->ip = '0.0.0.0';
        $this->city = 'Tallahassee';
        $this->latitude = '30.43826';
        $this->longitude = '-84.28073';
        $this->region = 'Florida';
        $this->regionCode = 'FL';
        $this->regionName = 'Florida';
        $this->countryCode = 'US';
        $this->countryName = 'United States';
        $this->timezone = 'America/New_York';
    }

    /**
     * Fetch geo data.
     */
    private function fetch($host): mixed
    {
        try {
            $this->request->setHttpProvider();
            $request = $this->request->getHttpClient()->request('GET', $host, [
                'timeout' => 5,
                'connect_timeout' => 3,
            ]);

            $contentType = $request->getHeader('Content-Type')[0] ?? '';
            $body = $request->getBody()->getContents();

            // Handle different response formats
            if (str_contains($contentType, 'application/json')) {
                return json_decode($body, true);
            } else {
                // Try to unserialize for PHP serialized data (GeoPlugin)
                $data = @unserialize($body);

                return $data !== false ? $data : json_decode($body, true);
            }
        } catch (GuzzleException $e) {
            Log::warning("GeoPlugin: HTTP request failed for {$this->currentProvider}", [
                'error' => $e->getMessage(),
                'url' => $host,
            ]);

            return false;
        }
    }

    /**
     * Parse GeoPlugin response
     */
    private function parseGeoPluginResponse($data): bool
    {
        if (! is_array($data) || empty($data['geoplugin_request'])) {
            return false;
        }

        $this->ip = $data['geoplugin_request'];
        $this->city = $data['geoplugin_city'] ?? null;
        $this->region = $data['geoplugin_region'] ?? null;
        $this->regionCode = $data['geoplugin_regionCode'] ?? null;
        $this->regionName = $data['geoplugin_regionName'] ?? null;
        $this->dmaCode = $data['geoplugin_dmaCode'] ?? null;
        $this->countryCode = $data['geoplugin_countryCode'] ?? null;
        $this->countryName = $data['geoplugin_countryName'] ?? null;
        $this->inEU = $data['geoplugin_inEU'] ?? null;
        $this->euVATrate = $data['geoplugin_euVATrate'] ?? false;
        $this->continentCode = $data['geoplugin_continentCode'] ?? null;
        $this->continentName = $data['geoplugin_continentName'] ?? null;
        $this->latitude = $data['geoplugin_latitude'] ?? null;
        $this->longitude = $data['geoplugin_longitude'] ?? null;
        $this->locationAccuracyRadius = $data['geoplugin_locationAccuracyRadius'] ?? null;
        $this->timezone = $data['geoplugin_timezone'] ?? null;
        $this->currencyCode = $data['geoplugin_currencyCode'] ?? null;
        $this->currencySymbol = $data['geoplugin_currencySymbol'] ?? null;
        $this->currencyConverter = $data['geoplugin_currencyConverter'] ?? null;

        return true;
    }

    /**
     * Parse IP-API response
     */
    private function parseIPAPIResponse($data): bool
    {
        if (! is_array($data) || ($data['status'] ?? '') !== 'success') {
            return false;
        }

        $this->ip = $data['query'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->region = $data['region'] ?? null;
        $this->regionCode = $data['region'] ?? null;
        $this->regionName = $data['regionName'] ?? null;
        $this->countryCode = $data['countryCode'] ?? null;
        $this->countryName = $data['country'] ?? null;
        $this->latitude = $data['lat'] ?? null;
        $this->longitude = $data['lon'] ?? null;
        $this->timezone = $data['timezone'] ?? null;

        return true;
    }

    /**
     * Parse IPStack response
     */
    private function parseIPStackResponse($data): bool
    {
        if (! is_array($data) || ! empty($data['error'])) {
            return false;
        }

        $this->ip = $data['ip'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->region = $data['region_name'] ?? null;
        $this->regionCode = $data['region_code'] ?? null;
        $this->regionName = $data['region_name'] ?? null;
        $this->countryCode = $data['country_code'] ?? null;
        $this->countryName = $data['country_name'] ?? null;
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;
        $this->timezone = $data['time_zone']['id'] ?? null;
        $this->continentCode = $data['continent_code'] ?? null;
        $this->continentName = $data['continent_name'] ?? null;

        return true;
    }

    /**
     * Parse IPInfo response
     */
    private function parseIPInfoResponse($data): bool
    {
        if (! is_array($data) || empty($data['ip'])) {
            return false;
        }

        $this->ip = $data['ip'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->region = $data['region'] ?? null;
        $this->regionCode = $data['region'] ?? null;
        $this->regionName = $data['region'] ?? null;
        $this->countryCode = $data['country'] ?? null;
        $this->countryName = $data['country'] ?? null;
        $this->timezone = $data['timezone'] ?? null;

        // IPInfo provides location as "lat,lon" string
        if (! empty($data['loc'])) {
            $coords = explode(',', $data['loc']);
            if (count($coords) === 2) {
                $this->latitude = trim($coords[0]);
                $this->longitude = trim($coords[1]);
            }
        }

        return true;
    }

    /**
     * Legacy method for backward compatibility
     *
     * @deprecated Use parseGeoPluginResponse instead
     */
    private function setData($data)
    {
        return $this->parseGeoPluginResponse($data);
    }
}

/*
"geoplugin_request" => "68.63.24.33"
  "geoplugin_city" => "Tallahassee"
  "geoplugin_region" => "Florida"
  "geoplugin_regionCode" => "FL"
  "geoplugin_regionName" => "Florida"
  "geoplugin_areaCode" => ""
  "geoplugin_dmaCode" => "530"
  "geoplugin_countryCode" => "US"
  "geoplugin_countryName" => "United States"
  "geoplugin_inEU" => 0
  "geoplugin_euVATrate" => false
  "geoplugin_continentCode" => "NA"
  "geoplugin_continentName" => "North America"
  "geoplugin_latitude" => "30.4274"
  "geoplugin_longitude" => "-84.258"
  "geoplugin_locationAccuracyRadius" => "10"
  "geoplugin_timezone" => "America/New_York"
  "geoplugin_currencyCode" => "USD"
  "geoplugin_currencySymbol" => "&#36;"
  "geoplugin_currencySymbol_UTF8" => "$"
  "geoplugin_currencyConverter" => "1"

 */
