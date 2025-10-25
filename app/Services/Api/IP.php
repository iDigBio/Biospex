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

/**
 * Class IP
 * Enhanced IP geolocation service with multiple providers and fallback mechanisms
 */
class IP
{
    /**
     * Multiple geolocation service providers with fallback support
     *
     * @var array
     */
    private $providers = [
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
    public $currentProvider = 'ipapi';

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
    public $latitude = null;

    /**
     * @var null
     */
    public $longitude = null;

    /**
     * GeoHelper constructor.
     */
    public function __construct(protected HttpRequest $request, protected App $app, protected City $cityModel) {}

    /**
     * Locate ip with enhanced detection and fallback mechanisms.
     * If not production environment, return random city.
     *
     * @param  null  $ip
     *
     * @throws \JsonException
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
            $this->setFallbackLocation();

            return false;
        }

        // Try each provider until one succeeds
        foreach ($this->providers as $providerName => $providerUrl) {
            $this->currentProvider = $providerName;

            if ($this->tryProvider($providerUrl, $ip)) {
                return true;
            }
        }

        // All providers failed, use fallback
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

        // Fallback to server IP if no valid client IP found
        return $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? '127.0.0.1';
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
            return false;
        }
    }

    /**
     * Set fallback location (Tallahassee, FL) with server IP
     */
    private function setFallbackLocation(): void
    {
        $this->ip = $this->detectRealIP();
        $this->city = 'Tallahassee';
        $this->latitude = '30.43826';
        $this->longitude = '-84.28073';
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
                // Try to unserialize for PHP serialized data, fallback to JSON
                $data = @unserialize($body);

                return $data !== false ? $data : json_decode($body, true);
            }
        } catch (GuzzleException $e) {

            return false;
        }
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
        $this->latitude = $data['lat'] ?? null;
        $this->longitude = $data['lon'] ?? null;

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
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;

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
}
