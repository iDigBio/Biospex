<?php namespace Biospex\Services\Curl;

/**
     * Request.php
     *
     * @package    Biospex Package
     * @version    1.0
     * @author     Robert Bruhn <bruhnrp@gmail.com>
     * @license    GNU General Public License, version 3
     * @copyright  (c) 2014, Biospex
     * @link       http://biospex.org
     *
     * This file is part of Biospex.
     * Biospex is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * Biospex is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
     */

/**
 * Class that represent a single curl request
 */
class Request
{
    public $url = false;
    public $method = 'GET';
    public $post_data = null;
    public $headers = null;
    public $options = null;

    /**
     * Constructor.
     *
     * @param $url
     * @param string $method
     * @param null $post_data
     * @param null $headers
     * @param null $options
     */
    public function __construct($url, $method = "GET", $post_data = null, $headers = null, $options = null)
    {
        $this->url = $url;
        $this->method = $method;
        $this->post_data = $post_data;
        $this->headers = $headers;
        $this->options = $options;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        unset($this->url, $this->method, $this->post_data, $this->headers, $this->options);
    }
}
