<?php namespace Biospex\Http\Controllers;
/**
 * SessionsController.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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

use Illuminate\Events\Dispatcher;
use Biospex\Repositories\Contracts\SessionInterface;
use Biospex\Http\Requests\LoginFromRequest;
use Illuminate\Support\Facades\Redirect;

class SessionsController extends Controller {

    /**
     * Member Vars
     */
    protected $session;

	/**
	 * Constructor
	 *
	 * @param Dispatcher $events
	 * @param SessionInterface $session
	 * @param LoginForm $loginForm
	 */
	public function __construct (Dispatcher $events, SessionInterface $session)
    {
		$this->events = $events;
        $this->session = $session;
    }

    /**
     * Show the login form
     */
    public function create ()
    {
        return view('sessions.login');
    }

    /**
     * Store a newly created resource in storage.
     * @param LoginFromRequest $request
     * @return mixed
     */
    public function store (LoginFromRequest $request)
    {
        $this->events->fire('user.login', [
            'userId' => $request->get['sessionData']['userId'],
            'email' => $request->get['sessionData']['email']
        ]);

        return Redirect::route('projects.index');
    }

	/**
	 * Delete user session
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
    public function destroy ()
    {
        $this->session->destroy();
		$this->events->fire('user.logout');
        return Redirect::route('home');
    }

}
