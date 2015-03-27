<?php namespace Biospex\Services\Curl;
    /**
     * Curl.php
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
 * Class that holds a rolling queue of curl requests.
 *
 * @throws CurlException
 */
class Curl {

    /**
     * @var int
     *
     * Window size is the max number of simultaneous connections allowed.
     *
     * REMEMBER TO RESPECT THE SERVERS:
     * Sending too many requests at one time can easily be perceived
     * as a DOS attack. Increase this window_size if you are making requests
     * to multiple servers or have permission from the receving server admins.
     */
    private $window_size = 5;

    /**
     * @var float
     *
     * Timeout is the timeout used for curl_multi_select.
     */
    private $timeout = 10;

    /**
     * @var string|array
     *
     * Callback function to be applied to each result.
     */
    private $callback;

    /**
     * @var array
     *
     * Set your base options that you want to be used with EVERY request.
     */
    protected $options = [
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 30
    ];

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var Request[]
     *
     * The request queue
     */
    private $requests = [];

    /**
     * @var RequestMap[]
     *
     * Maps handles to request indexes
     */
    private $requestMap = [];

    /**
     * Constructor.
     *
     * Callback function to be applied to each result.
     *
     * Can be specified as 'my_callback_function'
     * or array($object, 'my_callback_method').
     *
     * Function should take three parameters: $response, $info, $request.
     * $response is response body, $info is additional curl info.
     * $request is the original request
     *
     * @param null $callback
     */
    function __construct($callback = null)
    {
        $this->callback = $callback;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (isset($this->{$name})) ? $this->{$name} : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function __set($name, $value)
    {
        // append the base options & headers
        if ($name == "options" || $name == "headers")
        {
            $this->{$name} = $value + $this->{$name};
        }
        else
        {
            $this->{$name} = $value;
        }

        return true;
    }

    /**
     * Add a request to the request queue
     *
     * @param Request $request
     * @return bool
     */
    public function add($request)
    {
        $this->requests[] = $request;

        return true;
    }

    /**
     * Create new Request and add it to the request queue
     *
     * @param string $url
     * @param string $method
     * @param  $post_data
     * @param  $headers
     * @param  $options
     * @return bool
     */
    public function request($url, $method = "GET", $post_data = null, $headers = null, $options = null)
    {
        $this->requests[] = new Request($url, $method, $post_data, $headers, $options);

        return true;
    }

    /**
     * Perform GET request
     *
     * @param string $url
     * @param  $headers
     * @param  $options
     * @return bool
     */
    public function get($url, $headers = null, $options = null)
    {
        return $this->request($url, "GET", null, $headers, $options);
    }

    /**
     * Perform POST request
     *
     * @param string $url
     * @param  $post_data
     * @param  $headers
     * @param  $options
     * @return bool
     */
    public function post($url, $post_data = null, $headers = null, $options = null)
    {
        return $this->request($url, "POST", $post_data, $headers, $options);
    }

    /**
     * Execute the curl
     *
     * @param int $window_size Max number of simultaneous connections
     * @return string|bool
     */
    public function execute($window_size = null)
    {
        // rolling curl window must always be greater than 1
        if (sizeof($this->requests) == 1)
        {
            return $this->single_curl();
        }
        else
        {
            // start the rolling curl. window_size is the max number of simultaneous connections
            return $this->rolling_curl($window_size);
        }
    }

    /**
     * Performs a single curl request
     *
     * @access private
     * @return string
     */
    private function single_curl()
    {
        $ch = curl_init();
        $request = array_shift($this->requests);
        $options = $this->get_options($request);
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);

        // it's not neccesary to set a callback for one-off requests
        if ($this->callback)
        {
            $callback = $this->callback;
            if (is_callable($this->callback))
            {
                call_user_func($callback, $output, $info, $request);
            }
        }
        else
            return $output;

        return true;
    }

    /**
     * Performs multiple curl requests
     *
     * @access private
     * @throws CurlException
     * @param int $window_size Max number of simultaneous connections
     * @return bool
     */
    private function rolling_curl($window_size = null)
    {
        if ($window_size)
            $this->window_size = $window_size;

        // make sure the rolling window isn't greater than the # of urls
        if (sizeof($this->requests) < $this->window_size)
            $this->window_size = sizeof($this->requests);

        if ($this->window_size < 2)
        {
            throw new CurlException("Window size must be greater than 1");
        }

        $master = curl_multi_init();

        // start the first batch of requests
        for ($i = 0; $i < $this->window_size; $i++)
        {
            $ch = curl_init();

            $options = $this->get_options($this->requests[$i]);

            curl_setopt_array($ch, $options);
            curl_multi_add_handle($master, $ch);

            // Add to our request Maps
            $key = (string) $ch;
            $this->requestMap[$key] = $i;
        }

        do
        {
            while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM) ;
            if ($execrun != CURLM_OK)
            {
                break;
            }
            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($master))
            {
                // get the info and content returned on the request
                $info = curl_getinfo($done['handle']);
                $output = curl_multi_getcontent($done['handle']);

                // send the return values to the callback function.
                $callback = $this->callback;
                if (is_callable($callback))
                {
                    $key = (string) $done['handle'];
                    $request = $this->requests[$this->requestMap[$key]];
                    unset($this->requestMap[$key]);
                    call_user_func($callback, $output, $info, $request);
                }

                // start a new request (it's important to do this before removing the old one)
                if ($i < sizeof($this->requests) && isset($this->requests[$i]) && $i < count($this->requests))
                {
                    $ch = curl_init();
                    $options = $this->get_options($this->requests[$i]);
                    curl_setopt_array($ch, $options);
                    curl_multi_add_handle($master, $ch);

                    // Add to our request Maps
                    $key = (string) $ch;
                    $this->requestMap[$key] = $i;
                    $i++;
                }

                // remove the curl handle that just completed
                curl_multi_remove_handle($master, $done['handle']);

            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($running)
            {
                curl_multi_select($master, $this->timeout);
            }

        } while ($running);
        curl_multi_close($master);

        return true;
    }


    /**
     * Helper function to set up a new request by setting the appropriate options
     *
     * @access private
     * @param Request $request
     * @return array
     */
    private function get_options($request)
    {
        // options for this entire curl object
        $options = $this->__get('options');
        // NOTE: The PHP cURL library won't follow redirects if either safe_mode is on
        // or open_basedir is defined.
        // See: https://bugs.php.net/bug.php?id=30609
        if ((ini_get('safe_mode') == 'Off' || ! ini_get('safe_mode'))
            && ini_get('open_basedir') == ''
        )
        {
            $options[CURLOPT_FOLLOWLOCATION] = 1;
            $options[CURLOPT_MAXREDIRS] = 5;
        }
        $headers = $this->__get('headers');
        if ($request->headers)
        {
            $headers = $request->headers + $headers;
        }

        // append custom options for this specific request
        if ($request->options)
        {
            $options = $request->options + $options;
        }

        // set the request URL
        $options[CURLOPT_URL] = $request->url;

        // posting data w/ this request?
        if ($request->post_data)
        {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $request->post_data;
        }
        if ($headers)
        {
            $options[CURLOPT_HEADER] = 0;
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        // Due to a bug in cURL CURLOPT_WRITEFUNCTION must be defined as the last option
        // Otherwise it doesn't register. So let's unset and set it again
        // See http://stackoverflow.com/questions/15937055/curl-writefunction-not-being-called
        if ( ! empty($options[CURLOPT_WRITEFUNCTION]))
        {
            $writeCallback = $options[CURLOPT_WRITEFUNCTION];
            unset($options[CURLOPT_WRITEFUNCTION]);
            $options[CURLOPT_WRITEFUNCTION] = $writeCallback;
        }

        return $options;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        unset($this->window_size, $this->callback, $this->options, $this->headers, $this->requests);
    }
}