<?php namespace App\Repositories;

/**
 * AuthSession.php
 *
 * @package    Biospex Package
 * @version    2.0
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

use App\Repositories\Contracts\Auth;
use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Users\UserNotActivatedException;
use Cartalyst\Sentry\Throttling\UserSuspendedException;
use Cartalyst\Sentry\Throttling\UserBannedException;

class AuthSession extends Repository implements Auth
{
    /**
     * @var \Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * @var
     */
    protected $throttleProvider;


    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;

        // Get the Throttle Provider
        $this->throttleProvider = $this->sentry->getThrottleProvider();

        // Enable the Throttling Feature
        $this->throttleProvider->enable();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $data
     * @return array
     */
    public function store($data)
    {
        $result = [];
        try {
            // Check for 'rememberMe' in POST data
            if (!array_key_exists('rememberMe', $data)) {
                $data['rememberMe'] = 0;
            }

            //Check for suspension or banned status
            $user = $this->sentry->getUserProvider()->findByLogin(e($data['email']));
            $throttle = $this->throttleProvider->findByUserId($user->id);
            $throttle->check();

            // Set login credentials
            $credentials = [
                'email'    => e($data['email']),
                'password' => e($data['password'])
            ];

            // Try to authenticate the user
            $user = $this->sentry->authenticate($credentials, e($data['rememberMe']));

            $result['success'] = true;
            $result['userId'] = $user->id;
            $result['email'] = $user->email;
        } catch (UserNotFoundException $e) {
            // Sometimes a user is found, however hashed credentials do
            // not match. Therefore a user technically doesn't exist
            // by those credentials. Check the error message returned
            // for more information.
            $result['success'] = false;
            $result['message'] = trans('sessions.invalid');
        } catch (UserNotActivatedException $e) {
            $result['success'] = false;
            $url = route('auth.activation');
            $result['message'] = trans('sessions.notactive', ['url' => $url]);
        }

        // The following is only required if throttle is enabled
        catch (UserSuspendedException $e) {
            $time = $throttle->getSuspensionTime();
            $result['success'] = false;
            $result['message'] = trans('sessions.suspended');
        } catch (UserBannedException $e) {
            $result['success'] = false;
            $result['message'] = trans('sessions.banned');
        }

        return $result;
    }

    /**
     * Destroy user.
     *
     * @param null $id
     * @return mixed
     */
    public function destroy($id = null)
    {
        $this->sentry->logout();

        return;
    }
}
