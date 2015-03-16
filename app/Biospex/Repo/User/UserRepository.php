<?php namespace Biospex\Repo\User;
/**
 * UserRepository.php
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

use Biospex\Repo\Repository;
use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Cartalyst\Sentry\Users\UserExistsException;
use Cartalyst\Sentry\Groups\GroupNotFoundException;
use Biospex\Repo\Permission\PermissionInterface;
use Biospex\Repo\Invite\InviteInterface;
use Mockery\CountValidator\Exception;
use User;

class UserRepository extends Repository implements UserInterface {

    /**
     * @var \Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * @var \Biospex\Repo\Permission\PermissionInterface
     */
    protected $permission;

	/**
	 * Construct a new User Object
	 *
	 * @param Sentry $sentry
	 * @param PermissionInterface $permission
	 * @param InviteInterface $invite
	 */
	public function __construct(Sentry $sentry, PermissionInterface $permission, InviteInterface $invite)
	{
		$this->sentry = $sentry;
        $this->permission = $permission;
        $this->invite = $invite;
		$this->throttleProvider = $this->sentry->getThrottleProvider();
		$this->throttleProvider->enable();
	}

    /**
     * Return all the registered users
     *
     * @param array $columns
     * @return array|mixed
     */
	public function all ($columns = ['*'])
    {
        $users = $this->sentry->findAllUsers();

        foreach ($users as $user) {
            if ($user->isActivated())
            {
                $user->status = "Active";
            }
            else
            {
                $user->status = "Not Active";
            }

            //Pull Suspension & Ban info for this user
            $throttle = $this->throttleProvider->findByUserId($user->id);

            //Check for suspension
            if($throttle->isSuspended())
            {
                // User is Suspended
                $user->status = "Suspended";
            }

            //Check for ban
            if($throttle->isBanned())
            {
                // User is Banned
                $user->status = "Banned";
            }
        }

        return $users;
    }

	/**
	 * Return a specific user from the given id
	 *
	 * @param $id
	 * @param array $columns
	 * @return bool|\Cartalyst\Sentry\Users\UserInterface|mixed
	 */
	public function find ($id, $columns = ['*'])
    {
        try
        {
            $user = $this->sentry->findUserById($id);
        }
        catch (UserNotFoundException $e)
        {
            return false;
        }
        return $user;
    }

    /**
     * @return \Cartalyst\Sentry\Users\UserInterface
     */
    public function getUser()
    {
        return $this->sentry->getUser();
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param array $data
	 * @return array|mixed
	 */
	public function create ($data = [])
	{
		$result = [];
		$register = isset($data['registeruser']) ? false : true;
		try {
			//Attempt to register the user. 
			$user = $this->sentry->register([
				'email'    => e($data['email']),
				'password' => e($data['password']),
			], $register);
			$user->profile->first_name = e($data['first_name']);
			$user->profile->last_name = e($data['last_name']);
			$user->profile->save();

            // Add to Users group
            $usersGroup = $this->sentry->findGroupByName('Users');
            $user->addGroup($usersGroup);

            // Determine group creation: invite vs admin select vs admin create vs create from email
			if (isset($data['invite']) && !empty($data['invite']))
            {
                $invite = $this->invite->findByCode($data['invite']);
                if ($invite->email == $user->email)
                {
                    $group = $this->sentry->findGroupById($invite->group_id);
                    $user->addGroup($group);
                    $this->invite->destroy($invite->id);
                }
                else
                {
                    Session::flash('warning', trans('groups.invite_email_mismatch'));
                }
            }
            elseif ( ! empty($data['group']))
            {
                if ($data['group'] == 'new')
                {
                    $userGroup = $this->sentry->createGroup([
                        'user_id' => $user->id,
                        'name' => $data['new_group'],
                        'permissions' => [],
                    ]);
                    $user->addGroup($userGroup);
                }
                else
                {
                    $group = $this->sentry->findGroupById($data['group']);
                    $user->addGroup($group);
                }
            }
            else
            {
                // Create user group based on email
				$parts = explode("@", $user->email);
                $name = preg_replace('/[^a-zA-Z0-9]/', '', $parts[0]);
				$userGroup = $this->sentry->createGroup([
					'user_id' => $user->id,
					'name' => $name,
					'permissions' => [],
				]);
                $user->addGroup($userGroup);
            }

			//success!
	    	$result['success'] = true;
	    	$result['message'] = trans('users.created');
	    	$result['mailData']['activationCode'] = $user->GetActivationCode();
			$result['mailData']['userId'] = $user->id;
			$result['mailData']['email'] = $user->email;
		}
		catch (LoginRequiredException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.loginreq');
		}
		catch (UserExistsException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.exists');
		}
        catch (GroupNotFoundException $e)
        {
            $result['success'] = false;
            $result['message'] = trans('groups.notfound');
        } catch (Exception $e)
		{
			$result['success'] = false;
			$result['message'] = $e->getMessage();
		}

		return $result;
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  array $data
	 * @return Response
	 */
	public function update ($data = [])
	{
		$result = [];
		try
		{
		    // Find the user using the user id
		    $user = $this->sentry->findUserById($data['id']);

		    // Update the user details
			$user->profile->first_name = e($data['first_name']);
			$user->profile->last_name = e($data['last_name']);
            $user->email = e($data['email']);
            $user->activated = isset($data['activated']) ? 1 : 0;

		    $operator = $this->sentry->getUser();
		    if ($operator->hasAccess('user_edit_groups'))
		    {
			    // Update group memberships
			    $allGroups = $this->sentry->getGroupProvider()->findAll();
			    foreach ($allGroups as $group)
			    {
			    	if (isset($data['groups'][$group->id])) 
	                {
	                    //The user should be added to this group
	                    $user->addGroup($group);
	                } else {
	                    // The user should be removed from this group
	                    $user->removeGroup($group);
	                }
			    }
			}

            if ($operator->hasAccess('user_edit_permissions'))
            {
                $user->permissions = $this->permission->setPermissions($data);
            }

		    // Update the user
		    if ($user->save())
		    {
				$user->profile->save();
		        // User information was updated
		        $result['success'] = true;
	    		$result['message'] = trans('users.updated');
		    }
		    else
		    {
		        // User information was not updated
		        $result['success'] = false;
	    		$result['message'] = trans('users.notupdated');
		    }
		}
		catch (UserExistsException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.exists');
		}
		catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}

		return $result;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		try
		{
		    // Find the user using the user id
		    $user = $this->sentry->findUserById($id);

		    // Delete the user
		    $user->delete();
		}
		catch (UserNotFoundException $e)
		{
		    return false;
		}
		return true;
	}

	/**
	 * Attempt activation for the specified user
	 * @param  int $id   
	 * @param  string $code 
	 * @return bool       
	 */
	public function activate($id, $code)
	{
		$result = [];
		try
		{
		    // Find the user using the user id
		    $user = $this->sentry->findUserById($id);

		    // Attempt to activate the user
		    if ($user->attemptActivation($code))
		    {
		        // User activation passed
		        $result['success'] = true;
		        $url = route('login');
				$result['message'] = trans('users.activated', ['url' => $url]);
		    }
		    else
		    {
		        // User activation failed
		        $result['success'] = false;
	    		$result['message'] = trans('users.notactivated');
		    }
		}
		catch (UserExistsException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.exists');
		}
		catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
		return $result;
	}

	/**
	 * Resend the activation email to the specified email address
	 * @param  Array $data
	 * @return Response
	 */
	public function resend($data)
	{
		$result = [];
		try {
            //Attempt to find the user. 
            $user = $this->sentry->getUserProvider()->findByLogin(e($data['email']));

            if (!$user->isActivated())
            {
                //success!
            	$result['success'] = true;
	    		$result['message'] = trans('users.emailconfirm');
	    		$result['mailData']['activationCode'] = $user->GetActivationCode();
                $result['mailData']['userId'] = $user->getId();
                $result['mailData']['email'] = e($data['email']);
            }
            else 
            {
                $result['success'] = false;
	    		$result['message'] = trans('users.alreadyactive');
            }

	    }
	    catch (UserExistsException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.exists');
		}
		catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
	    return $result;
	}

	/**
	 * Handle a password reset rewuest
	 * @param  Array $data 
	 * @return Bool       
	 */
	public function forgotPassword($data)
	{
		$result = [];
		try
        {
			$user = $this->sentry->getUserProvider()->findByLogin(e($data['email']));

	        $result['success'] = true;
	    	$result['message'] = trans('users.emailinfo');
	    	$result['mailData']['resetCode'] = $user->getResetPasswordCode();
			$result['mailData']['userId'] = $user->getId();
			$result['mailData']['email'] = e($data['email']);
        }
        catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
        return $result;
	}

	/**
	 * Process the password reset request
	 * @param  int $id   
	 * @param  string $code 
	 * @return Array
	 */
	public function resetPassword($id, $code)
	{
		$result = [];
		try
        {
	        // Find the user
	        $user = $this->sentry->getUserProvider()->findById($id);
	        $newPassword = $this->_generatePassword(8,8);

			// Attempt to reset the user password
			if ($user->attemptResetPassword($code, $newPassword))
			{
				// Email the reset code to the user
	        	$result['success'] = true;
		    	$result['message'] = trans('users.emailpassword');
		    	$result['mailData']['newPassword'] = $newPassword;
		    	$result['mailData']['email'] = $user->getLogin();
 			}
			else
			{
				// Password reset failed
				$result['success'] = false;
				$result['message'] = trans('users.problem');
			}
        }
       catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
        return $result;
	}

	/**
	 * Process a change password request.
	 *
	 * @param $data
	 * @return array
	 */
	public function changePassword($data)
	{
		$result = [];
		try
		{
			$user = $this->sentry->getUserProvider()->findById($data['id']);        
		
			if ($user->checkHash(e($data['oldPassword']), $user->getPassword()))
			{
				//The oldPassword matches the current password in the DB. Proceed.
				$user->password = e($data['newPassword']);

				if ($user->save())
				{
					// User saved
					$result['success'] = true;
					$result['message'] = trans('users.passwordchg');
				}
				else
				{
					// User not saved
					$result['success'] = false;
					$result['message'] = trans('users.passwordprob');
				}
			} 
			else 
			{
		        // Password mismatch. Abort.
		        $result['success'] = false;
				$result['message'] = trans('users.oldpassword');
			}                                        
		}
		catch (LoginRequiredException $e)
		{
			$result['success'] = false;
			$result['message'] = 'Login field required.';
		}
		catch (UserExistsException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.exists');
		}
		catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
		return $result;
	}

	/**
	 * Suspend a user
	 * @param  int $id      
	 * @param  int $minutes 
	 * @return Array          
	 */
	public function suspend($id, $minutes)
	{
		$result = [];
		try
		{
		    // Find the user using the user id
		    $throttle = $this->sentry->findThrottlerByUserId($id);

		    //Set suspension time
            $throttle->setSuspensionTime($minutes);

		    // Suspend the user
		    $throttle->suspend();

		    $result['success'] = true;
			$result['message'] = trans('users.suspended', ['minutes' => $minutes]);
		}
		catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
		return $result;
	}

	/**
	 * Remove a users' suspension.
	 * @param $id
	 * @return array
	 */
	public function unSuspend($id)
	{
		$result = [];
		try
		{
		    // Find the user using the user id
		    $throttle = $this->sentry->findThrottlerByUserId($id);

		    // Unsuspend the user
		    $throttle->unsuspend();

		    $result['success'] = true;
			$result['message'] = trans('users.unsuspended');
		}
		catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
		return $result;
	}

	/**
	 * Ban a user
	 * @param  int $id 
	 * @return Array     
	 */
	public function ban($id)
	{
		$result = [];
		try
		{
		    // Find the user using the user id
		    $throttle = $this->sentry->findThrottlerByUserId($id);

		    // Ban the user
		    $throttle->ban();

		    $result['success'] = true;
			$result['message'] = trans('users.banned');
		}
		catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
		return $result;
	}

	/**
	 * Remove a users' ban
	 * @param  int $id 
	 * @return Array     
	 */
	public function unBan($id)
	{
		$result = [];
		try
		{
		    // Find the user using the user id
		    $throttle = $this->sentry->findThrottlerByUserId($id);

		    // Unban the user
		    $throttle->unBan();

		    $result['success'] = true;
			$result['message'] = trans('users.unbanned');
		}
		catch (UserNotFoundException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('users.notfound');
		}
		return $result;
	}

	/**
	 * Generate password - helper function
	 * From http://www.phpscribble.com/i4xzZu/Generate-random-passwords-of-given-length-and-strength
	 *
	 * @param int $length
	 * @param int $strength
	 * @return string
	 */
    private function _generatePassword($length=9, $strength=4) {
        $vowels = 'aeiouy';
        $consonants = 'bcdfghjklmnpqrstvwxz';
        if ($strength & 1) {
               $consonants .= 'BCDFGHJKLMNPQRSTVWXZ';
        }
        if ($strength & 2) {
               $vowels .= "AEIOUY";
        }
        if ($strength & 4) {
               $consonants .= '23456789';
        }
        if ($strength & 8) {
               $consonants .= '@#$%';
        }

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }
}
