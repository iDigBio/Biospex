<?php

/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Api;

use App\Services\Requests\HttpRequest;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class GeoPlugin
 */
class GeoPlugin
{
    /**
     * Plugin server.
     *
     * @var string
     */
    private $host = 'http://www.geoplugin.net/php.gp?ip={IP}&lang={LANG}';

    /**
     * Default language.
     * supported languages: de, en, es, fr, ja, pt-BR, ru, zh-CN
     *
     * @var string
     */
    private $lang = 'en';

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
    public function __construct(protected HttpRequest $request) {}

    /**
     * Locate ip.
     *
     * @param  null  $ip
     */
    public function locate($ip = null): bool
    {
        if (is_null($ip)) {
            if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
                //ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                //ip pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }

        $host = str_replace('{IP}', $ip, $this->host);
        $host = str_replace('{LANG}', $this->lang, $host);

        $response = $this->fetch($host);

        if (! $response) {
            return false;
        }

        $this->setData($response);

        return true;
    }

    /**
     * Fetch geo data.
     *
     * @return mixed
     */
    private function fetch($host)
    {
        try {
            $this->request->setHttpProvider();
            $request = $this->request->getHttpClient()->request('GET', $host);

            return unserialize($request->getBody()->getContents());
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * Set data.
     */
    private function setData($data)
    {
        $this->ip = $data['geoplugin_request'];
        $this->city = $data['geoplugin_city'];
        $this->region = $data['geoplugin_region'];
        $this->regionCode = $data['geoplugin_regionCode'];
        $this->regionName = $data['geoplugin_regionName'];
        $this->dmaCode = $data['geoplugin_dmaCode'];
        $this->countryCode = $data['geoplugin_countryCode'];
        $this->countryName = $data['geoplugin_countryName'];
        $this->inEU = $data['geoplugin_inEU'];
        $this->euVATrate = $data['geoplugin_euVATrate'];
        $this->continentCode = $data['geoplugin_continentCode'];
        $this->continentName = $data['geoplugin_continentName'];
        $this->latitude = $data['geoplugin_latitude'];
        $this->longitude = $data['geoplugin_longitude'];
        $this->locationAccuracyRadius = $data['geoplugin_locationAccuracyRadius'];
        $this->timezone = $data['geoplugin_timezone'];
        $this->currencyCode = $data['geoplugin_currencyCode'];
        $this->currencySymbol = $data['geoplugin_currencySymbol'];
        $this->currencyConverter = $data['geoplugin_currencyConverter'];
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
