<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Permission;
use App\Repositories\Contracts\Invite;
use App\Jobs\ShowRegistrationFormJob;
use App\Jobs\PostRegistrationFormJob;
use App\Http\Requests\RegisterFormRequest;
use App\Services\Common\UserService;

class UsersController extends Controller
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var Permission
     */
    protected $permission;
    /**
     * @var UserService
     */
    private $service;

    /**
     * Instantiate a new UsersController
     *
     * @param UserService $service
     * @param Dispatcher $events
     * @param User $user
     * @param Group $group
     * @param Permission $permission
     * @param Invite $invite
     */
    public function __construct(
        UserService $service,
        Dispatcher $events,
        User $user,
        Group $group,
        Permission $permission,
        Invite $invite
    ) {
        $this->events = $events;
        $this->user = $user;
        $this->group = $group;
        $this->permission = $permission;
        $this->invite = $invite;
        $this->service = $service;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $vars = $this->service->index();

        return view('users.index', $vars);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return Response
     */
    public function create()
    {
        $allGroups = $this->group->findAllGroups();
        $groups = $this->group->selectOptions($allGroups, true);
        $cancel = URL::route('users.index');

        return \View::make('users.create', compact('groups', 'cancel'));
    }

    /**
     * Store a newly created user.
     *
     * @param RegisterFormRequest $request
     * @return Response
     */
    public function store(RegisterFormRequest $request)
    {
        $result = $this->dispatch(new PostRegistrationFormJob($request));

        if ($result && $request->exists('registeruser')) {
            return \Redirect::action('login');
        }

        if ($result) {
            return \Redirect::action('users.edit', [$result['userId']]);
        }

        return \Redirect::back()->withInput();
    }

    /**
     * \Redirect to edit page.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return \Redirect::action('users.edit', [$id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $user = \Sentry::findUserById($id);

        if (is_null($user) || ! is_numeric($id)) {
            \Session::flash('error', trans('pages.error_missing_variable'));

            return \Redirect::route('home');
        }

        $groups = $user->groups->toArray();
        $userGroups = array_map(function ($groups) {
            return $groups['name'];
        }, $groups);
        $allGroups = $this->group->all();
        $timezones = timezone_select();

        // Get all permissions
        $permissions = $this->permission->getPermissionsGroupBy();
        $userPermissions = $user->permissions;
        $userEditPermissions = $this->user->getUser()->hasAccess('user_edit_permissions');
        $userEditGroups = $this->user->getUser()->hasAccess('user_edit_groups');
        $superUser = $this->user->getUser()->isSuperUser();
        $cancel = $this->user->getUser()->isSuperUser() ? \URL::route('users.index') : \URL::route('projects.index');

        return \View::make('users.edit', compact(
                'user',
                'timezones',
                'userEditPermissions',
                'allGroups',
                'userGroups',
                'permissions',
                'userPermissions',
                'userEditGroups',
                'superUser',
                'cancel'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        // Form Processing
        $result = $this->userForm->update(\Input::all());

        if ($result['success']) {
            $this->events->fire('user.updated', ['userId' => $id]);

            // Success!
            \Session::flash('success', $result['message']);

            return \Redirect::action('users.edit', [$id]);
        } else {
            \Session::flash('error', $result['message']);

            return \Redirect::action('users.edit', [$id])
                ->withInput()
                ->withErrors($this->userForm->errors());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        if (! is_numeric($id)) {
            \Session::flash('error', trans('pages.error_delete_user'));

            return \Redirect::action('UsersController@index');
        }

        if ($this->user->destroy($id)) {
            $this->events->fire('user.destroyed', ['userId' => $id]);

            \Session::flash('success', trans('users.deleted'));

            return \Redirect::action('UsersController@index');
        } else {
            \Session::flash('error', trans('pages.error_delete_user'));

            return \Redirect::action('UsersController@index');
        }
    }

    /**
     * Process a password change request
     *
     * @param  int $id
     * @return redirect
     */
    public function change($id)
    {
        $data = \Input::all();
        $data['id'] = $id;

        // Form Processing
        $result = $this->changePasswordForm->change($data);

        if ($result['success']) {
            $this->events->fire('user.passwordchange', ['userId' => $id]);

            // Success!
            \Session::flash('success', $result['message']);

            return \Redirect::action('UsersController@show', [$id]);
        } else {
            \Session::flash('error', $result['message']);

            return \Redirect::action('UsersController@edit', [$id])
                ->withInput()
                ->withErrors($this->changePasswordForm->errors());
        }
    }
}
