<?php
/**
 * UsersController.php
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

use Biospex\Repo\User\UserInterface;
use Biospex\Repo\Group\GroupInterface;
use Biospex\Form\Register\RegisterForm;
use Biospex\Form\User\UserForm;
use Biospex\Form\ResendActivation\ResendActivationForm;
use Biospex\Form\ForgotPassword\ForgotPasswordForm;
use Biospex\Form\ChangePassword\ChangePasswordForm;
use Biospex\Form\SuspendUser\SuspendUserForm;
use Biospex\Repo\Permission\PermissionInterface;
use Biospex\Repo\Invite\InviteInterface;


class UsersController extends BaseController {

	protected $user;
	protected $group;
	protected $registerForm;
	protected $userForm;
	protected $resendActivationForm;
	protected $forgotPasswordForm;
	protected $changePasswordForm;
	protected $suspendUserForm;
    protected $permission;

	/**
	 * Instantiate a new UsersController
	 */
	public function __construct(
		UserInterface $user,
		GroupInterface $group,
		RegisterForm $registerForm, 
		UserForm $userForm,
		ResendActivationForm $resendActivationForm,
		ForgotPasswordForm $forgotPasswordForm,
		ChangePasswordForm $changePasswordForm,
		SuspendUserForm $suspendUserForm,
        PermissionInterface $permission,
        InviteInterface $invite
    )
	{
		$this->user = $user;
		$this->group = $group;
		$this->registerForm = $registerForm;
		$this->userForm = $userForm;
		$this->resendActivationForm = $resendActivationForm;
		$this->forgotPasswordForm = $forgotPasswordForm;
		$this->changePasswordForm = $changePasswordForm;
		$this->suspendUserForm = $suspendUserForm;
        $this->permission = $permission;
        $this->invite = $invite;

        // Establish Filters
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('hasUserAccess:user_view', array('only' => array('show', 'index')));
        $this->beforeFilter('hasUserAccess:user_edit', array('only' => array('edit', 'update')));
        $this->beforeFilter('hasUserAccess:user_delete', array('only' => array('destroy')));
        $this->beforeFilter('hasUserAccess:user_create', array('only' => array('create')));
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $users = $this->user->all();
      
        return View::make('users.index', compact('users'));
	}

    /**
     * Show the form for creating a new user.
     *
     * @param null $code
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function register($code = null)
    {
        $registration = Config::get('config.registration');

        if (!$registration)
        {   
            Session::flash('error', trans('users.inactive_reg'));
            return Redirect::route('home');
        }

        if ( ! empty($code))
        {
            if ( ! $invite = $this->invite->findByCode($code))
                Session::flash('warning', trans('groups.invite_not_found'));
        }
        $code = isset($invite->code) ? $invite->code : null;
        $email = isset($invite->email) ? $invite->email : null;

        $group = $this->group->byName("Users");
        $register = Route::currentRouteName() == 'register' ? true : false;

        return View::make('users.create', compact('register', 'group', 'code', 'email'));
    }

    /**
	 * Show the form for creating a new user.
	 *
	 * @return Response
	 */
	public function create()
	{
        $groups = $this->group->selectOptions();
        $group = $this->group->byName("Users");
        $register = Route::currentRouteName() == 'users.create' ? false : true;
        $email = null;

        return View::make('users.create', compact('register', 'groups', 'group', 'email'));
	}

	/**
	 * Store a newly created user.
	 *
	 * @return Response
	 */
	public function store()
	{
        // Form Processing
        $result = $this->registerForm->save(Input::all());

        if( $result['success'] )
        {
            Event::fire('user.registered', array(
            	'email' => $result['mailData']['email'], 
            	'userId' => $result['mailData']['userId'], 
                'activationCode' => $result['mailData']['activationCode']
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::action('users.show', array($result['mailData']['userId']));

        } else {
            Session::flash('error', $result['message']);
            return Redirect::back()->withInput()->withErrors($this->registerForm->errors());
        }
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $user = $this->user->find($id);

        if($user == null || !is_numeric($id))
            return \App::abort(404);

        $viewPermissions = Sentry::getUser()->hasAccess('permission_view');

        $userGroups = Sentry::getUser()->isSuperUser() ? $this->group->all() : $user->getGroups();
        foreach ($userGroups as $userGroup)
        {
            if ($userGroup->name == 'Users')
                continue;

            $groups[] = $userGroup;
            foreach ($userGroup->projects as $project)
            {
                $projects[] = $project;
            }
        }

        return View::make('users.show', compact('user', 'viewPermissions', 'groups', 'projects'));
    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
        $user = $this->user->find($id);

        if($user == null || !is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

        $groups = $user->groups->toArray();
        $userGroups = array_map(function ($groups){ return $groups['name']; }, $groups);
        $allGroups = $this->group->all();

        // Get all permissions
        $permissions = $this->permission->getPermissionsGroupBy();
        $userPermissions = $user->permissions;
        $userEditPermissions = Sentry::getUser()->hasAccess('user_edit_permissions');
        $userEditGroups = Sentry::getUser()->hasAccess('user_edit_groups');
        $superUser = Sentry::getUser()->isSuperUser();

        return View::make('users.edit', compact(
                'user',
                'userEditPermissions',
                'allGroups',
                'userGroups',
                'permissions',
                'userPermissions',
                'userEditGroups',
                'superUser'
            )
        );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

		// Form Processing
        $result = $this->userForm->update(Input::all());

        if( $result['success'] )
        {
            Event::fire('user.updated', array(
                'userId' => $id, 
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::action('UsersController@show', array($id));

        } else {
            Session::flash('error', $result['message']);
            return Redirect::action('UsersController@edit', array($id))
                ->withInput()
                ->withErrors( $this->userForm->errors() );
        }
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

		if ($this->user->destroy($id))
		{
			Event::fire('user.destroyed', array(
                'userId' => $id, 
            ));

            Session::flash('success', trans('users.deleted'));
            return Redirect::action('UsersController@index');
        }
        else 
        {
        	Session::flash('error', trans('errors.error_delete_user'));
            return Redirect::action('UsersController@index');
        }
	}

	/**
	 * Activate a new user
	 * @param  int $id   
	 * @param  string $code 
	 * @return Response
	 */
	public function activate($id, $code)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

		$result = $this->user->activate($id, $code);

        if( $result['success'] )
        {
            Event::fire('user.activated', array(
                'userId' => $id, 
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::route('home');

        } else {
            Session::flash('error', $result['message']);
            return Redirect::route('home');
        }
	}

	/**
	 * Process resend activation request
	 * @return Response
	 */
	public function resend()
	{
		// Form Processing
        $result = $this->resendActivationForm->resend( Input::all() );

        if( $result['success'] )
        {
            Event::fire('user.resend', array(
				'email' => $result['mailData']['email'], 
				'userId' => $result['mailData']['userId'], 
				'activationCode' => $result['mailData']['activationCode']
			));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::route('home');
        } 
        else 
        {
            Session::flash('error', $result['message']);
            return Redirect::route('resendActivationForm')
                ->withInput()
                ->withErrors( $this->resendActivationForm->errors() );
        }
	}

	/**
	 * Process Forgot Password request
	 * @return Response
	 */
	public function forgot()
	{
		// Form Processing
        $result = $this->forgotPasswordForm->forgot( Input::all() );

        if( $result['success'] )
        {
            Event::fire('user.forgot', array(
				'email' => $result['mailData']['email'],
				'userId' => $result['mailData']['userId'],
				'resetCode' => $result['mailData']['resetCode']
			));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::route('home');
        } 
        else 
        {
            Session::flash('error', $result['message']);
            return Redirect::route('forgotPasswordForm')
                ->withInput()
                ->withErrors( $this->forgotPasswordForm->errors() );
        }
	}

    /**
     * Process a password reset request link
     *
     * @param $id
     * @param $code
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function reset($id, $code)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

		$result = $this->user->resetPassword($id, $code);

        if( $result['success'] )
        {
            Event::fire('user.newpassword', array(
				'email' => $result['mailData']['email'],
				'newPassword' => $result['mailData']['newPassword']
			));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::route('home');

        } else {
            Session::flash('error', $result['message']);
            return Redirect::route('home');
        }
	}

	/**
	 * Process a password change request
	 * @param  int $id 
	 * @return redirect     
	 */
	public function change($id)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

		$data = Input::all();
		$data['id'] = $id;

		// Form Processing
        $result = $this->changePasswordForm->change( $data );

        if( $result['success'] )
        {
            Event::fire('user.passwordchange', array(
                'userId' => $id, 
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::action('UsersController@show', array($id));
        } 
        else 
        {
            Session::flash('error', $result['message']);
            return Redirect::action('UsersController@edit', array($id))
                ->withInput()
                ->withErrors( $this->changePasswordForm->errors() );
        }
	}

	/**
	 * Process a suspend user request
	 * @param  int $id 
	 * @return Redirect     
	 */
	public function suspend($id)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

		// Form Processing
        $result = $this->suspendUserForm->suspend( Input::all() );

        if( $result['success'] )
        {
            Event::fire('user.suspended', array(
                'userId' => $id, 
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::action('UsersController@index');

        } else {
            Session::flash('error', $result['message']);
            return Redirect::action('UsersController@suspend', array($id))
                ->withInput()
                ->withErrors( $this->suspendUserForm->errors() );
        }
	}

	/**
	 * Unsuspend user
	 * @param  int $id 
	 * @return Redirect     
	 */
	public function unsuspend($id)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

		$result = $this->user->unSuspend($id);

        if( $result['success'] )
        {
            Event::fire('user.unsuspended', array(
                'userId' => $id, 
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::action('UsersController@index');

        } else {
            Session::flash('error', $result['message']);
            return Redirect::action('UsersController@index');
        }
	}

	/**
	 * Ban a user
	 * @param  int $id 
	 * @return Redirect     
	 */
	public function ban($id)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }

		$result = $this->user->ban($id);

        if( $result['success'] )
        {
            Event::fire('user.banned', array(
                'userId' => $id, 
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::action('UsersController@index');

        } else {
            Session::flash('error', $result['message']);
            return Redirect::action('UsersController@index');
        }
	}

	public function unban($id)
	{
        if(!is_numeric($id))
        {
            // @codeCoverageIgnoreStart
            return \App::abort(404);
            // @codeCoverageIgnoreEnd
        }
        
		$result = $this->user->unBan($id);

        if( $result['success'] )
        {
            Event::fire('user.unbanned', array(
                'userId' => $id, 
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::action('UsersController@index');

        } else {
            Session::flash('error', $result['message']);
            return Redirect::action('UsersController@index');
        }
	}


}

	
