<?php

/**
 * ServerInfoController.php
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

class ServerInfoController extends BaseController
{

	/**
	 * Constructor
	 */
	public function __construct ()
	{
		$this->beforeFilter('auth');
	}

	/**
	 * Display php info
	 */
	public function showPhpInfo ()
	{
		if (!Sentry::getUser()->isSuperUser())
			return Redirect::route('login');

		ob_start();
		phpinfo();
		$pinfo = ob_get_contents();
		ob_end_clean();

		$info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);

		return View::make('info', compact(['info']));
	}

	public function clear ()
	{
		if (!Sentry::getUser()->isSuperUser())
			return Redirect::route('login');

		Cache::flush();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, Config::get('config.ip') . '/cache.php');
		curl_exec($ch);
		curl_close($ch);

		Session::flash('success', "Cache has been flushed.");

		return Redirect::intended('/projects');
	}

	public function example ()
	{
		return Redirect::route('projects.index')->with('message', 'Test page ran.');
	}
}
