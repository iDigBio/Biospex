<?php
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

use Biospex\Repo\Session\SessionInterface;
use Biospex\Form\Login\LoginForm;


class SessionsController extends BaseController {

    /**
     * Member Vars
     */
    protected $session;
    protected $loginForm;

    /**
     * Constructor
     */
    public function __construct (SessionInterface $session, LoginForm $loginForm)
    {
        $this->session = $session;
        $this->loginForm = $loginForm;
    }

    /**
     * Show the login form
     */
    public function create ()
    {
        return View::make('sessions.login');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store ()
    {
        // Form Processing
        $result = $this->loginForm->save(Input::all());

        if ($result['success']) {
            Event::fire('user.login', array(
                'userId' => $result['sessionData']['userId'],
                'email' => $result['sessionData']['email']
            ));

            // Success!
            return Redirect::intended(route('projects.index'));

        } else {
            Session::flash('error', $result['message']);
            return Redirect::route('login')
                ->withInput()
                ->withErrors($this->loginForm->errors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy ()
    {
        $this->session->destroy();
        Event::fire('user.logout');
        return Redirect::route('home');
    }

}
