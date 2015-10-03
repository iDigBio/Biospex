<?php namespace App\Repositories;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Cartalyst\Sentry\Users\UserAlreadyActivatedException;
use Cartalyst\Sentry\Users\UserExistsException;
use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Permission;
use App\Repositories\Contracts\Invite;

class UserRepository extends Repository implements User
{
    /**
     * @var \Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * @var Permission
     */
    protected $permission;

    /**
     * Construct a new User Object
     *
     * @param Sentry $sentry
     * @param Permission $permission
     * @param Invite $invite
     */
    public function __construct(Sentry $sentry, Permission $permission, Invite $invite)
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
    public function all($columns = ['*'])
    {
        $users = $this->sentry->findAllUsers();

        foreach ($users as $user) {
            if ($user->isActivated()) {
                $user->status = "Active";
            } else {
                $user->status = "Not Active";
            }

            //Pull Suspension & Ban info for this user
            $throttle = $this->throttleProvider->findByUserId($user->id);

            //Check for suspension
            if ($throttle->isSuspended()) {
                // User is Suspended
                $user->status = "Suspended";
            }

            //Check for ban
            if ($throttle->isBanned()) {
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
     * @return bool|\Cartalyst\Sentry\Users\User|mixed
     */
    public function find($id, $columns = ['*'])
    {
        try {
            $user = $this->sentry->findUserById($id);
        } catch (UserNotFoundException $e) {
            return false;
        }
        return $user;
    }

    /**
     * @return \Cartalyst\Sentry\Users\User
     */
    public function getUser()
    {
        return $this->sentry->getUser();
    }

    
    /**
     * Update the specified resource in storage.
     *
     * @param  array $data
     * @return Response
     */
    public function update($data = [])
    {
        $result = [];
        try {
            // Find the user using the user id
            $user = $this->sentry->findUserById($data['id']);

            // Update the user details
            $user->profile->first_name = e($data['first_name']);
            $user->profile->last_name = e($data['last_name']);
            $user->email = e($data['email']);
            $user->activated = isset($data['activated']) ? 1 : 0;

            $operator = $this->sentry->getUser();
            if ($operator->hasAccess('user_edit_groups')) {
                // Update group memberships
                $allGroups = $this->sentry->getGroupProvider()->findAll();
                foreach ($allGroups as $group) {
                    if (isset($data['groups'][$group->id])) {
                        //The user should be added to this group
                        $user->addGroup($group);
                    } else {
                        // The user should be removed from this group
                        $user->removeGroup($group);
                    }
                }
            }

            if ($operator->hasAccess('user_edit_permissions')) {
                $user->permissions = $this->permission->setPermissions($data);
            }

            // Update the user
            if ($user->save()) {
                $user->profile->save();
                // User information was updated
                $result['success'] = true;
                $result['message'] = trans('users.updated');
            } else {
                // User information was not updated
                $result['success'] = false;
                $result['message'] = trans('users.notupdated');
            }
        } catch (UserExistsException $e) {
            $result['success'] = false;
            $result['message'] = trans('users.exists');
        } catch (UserNotFoundException $e) {
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
        try {
            // Find the user using the user id
            $user = $this->sentry->findUserById($id);

            // Delete the user
            $user->delete();
        } catch (UserNotFoundException $e) {
            return false;
        }
        return true;
    }


    /**
     * Handle a password reset rewuest
     * @param  Array $data 
     * @return Bool       
     */
    public function forgotPassword($data)
    {
        $result = [];
        try {
            $user = $this->sentry->getUserProvider()->findByLogin(e($data['email']));

            $result['success'] = true;
            $result['message'] = trans('users.emailinfo');
            $result['mailData']['resetCode'] = $user->getResetPasswordCode();
            $result['mailData']['userId'] = $user->getId();
            $result['mailData']['email'] = e($data['email']);
        } catch (UserNotFoundException $e) {
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
        try {
            $user = $this->sentry->getUserProvider()->findById($data['id']);
        
            if ($user->checkHash(e($data['oldPassword']), $user->getPassword())) {
                //The oldPassword matches the current password in the DB. Proceed.
                $user->password = e($data['newPassword']);

                if ($user->save()) {
                    // User saved
                    $result['success'] = true;
                    $result['message'] = trans('users.passwordchg');
                } else {
                    // User not saved
                    $result['success'] = false;
                    $result['message'] = trans('users.passwordprob');
                }
            } else {
                // Password mismatch. Abort.
                $result['success'] = false;
                $result['message'] = trans('users.oldpassword');
            }
        } catch (LoginRequiredException $e) {
            $result['success'] = false;
            $result['message'] = 'Login field required.';
        } catch (UserExistsException $e) {
            $result['success'] = false;
            $result['message'] = trans('users.exists');
        } catch (UserNotFoundException $e) {
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
        try {
            // Find the user using the user id
            $throttle = $this->sentry->findThrottlerByUserId($id);

            //Set suspension time
            $throttle->setSuspensionTime($minutes);

            // Suspend the user
            $throttle->suspend();

            $result['success'] = true;
            $result['message'] = trans('users.suspended', ['minutes' => $minutes]);
        } catch (UserNotFoundException $e) {
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
        try {
            // Find the user using the user id
            $throttle = $this->sentry->findThrottlerByUserId($id);

            // Unsuspend the user
            $throttle->unsuspend();

            $result['success'] = true;
            $result['message'] = trans('users.unsuspended');
        } catch (UserNotFoundException $e) {
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
        try {
            // Find the user using the user id
            $throttle = $this->sentry->findThrottlerByUserId($id);

            // Ban the user
            $throttle->ban();

            $result['success'] = true;
            $result['message'] = trans('users.banned');
        } catch (UserNotFoundException $e) {
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
        try {
            // Find the user using the user id
            $throttle = $this->sentry->findThrottlerByUserId($id);

            // Unban the user
            $throttle->unBan();

            $result['success'] = true;
            $result['message'] = trans('users.unbanned');
        } catch (UserNotFoundException $e) {
            $result['success'] = false;
            $result['message'] = trans('users.notfound');
        }
        return $result;
    }
}
