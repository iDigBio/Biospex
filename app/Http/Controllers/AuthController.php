<?php namespace Biospex\Http\Controllers;
/**
 * AuthController.php
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

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Biospex\Http\Requests\UserLoginRequest;
use Biospex\Commands\UserLogInCommand;
use Biospex\Commands\UserLogOutCommand;

class AuthController extends Controller {

    /**
     * Constructor
     */
	public function __construct () {}

    /**
     * Show login form
     */
    public function create ()
    {
        return view('sessions.login');
    }

    /**
     * Check user login and store.
     *
     * @param UserLoginRequest $request
     * @return mixed
     */
    public function store (UserLoginRequest $request)
    {
        $input = $request->only('email', 'password', 'remember');
        $result = $this->dispatch(new UserLogInCommand($input));

        if ($result['success'])
            return Redirect::route('projects.index');

        Session::flash('error', $result['message']);

        Redirect::route('login')->withInput();
    }

    /**
     * Delete user session
     *
     * @return mixed
     */
    public function destroy ()
    {
        $this->dispatch(new UserLogOutCommand());

        Redirect::route('home');
    }
}
