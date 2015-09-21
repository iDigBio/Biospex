<?php
/**
 * GroupsController.php
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
use Cartalyst\Sentry\Sentry;
use Illuminate\Events\Dispatcher;
use Biospex\Repo\Group\GroupInterface;
use Biospex\Form\Group\GroupForm;
use Biospex\Repo\Permission\PermissionInterface;
use Cartalyst\Sentry\Groups\GroupNotFoundException;

class GroupsController extends BaseController
{
    /**
     * @var Sentry
     */
    protected $sentry;

    /**
     * User
     *
     * @var
     */
    protected $user;

    /**
     * Events dispatch
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var Biospex\Repo\Group\GroupInterface
     */
    protected $group;

    /**
     * @var Biospex\Form\Group\GroupForm
     */
    protected $groupForm;

    /**
     * @var Biospex\Repo\Permission\PermissionInterface
     */
    protected $permission;

    /**
     * Instantiate a new GroupsController
     *
     * @param Sentry $sentry
     * @param Dispatcher $events
     * @param GroupInterface $group
     * @param GroupForm $groupForm
     * @param PermissionInterface $permission
     */
    public function __construct(
        Sentry $sentry,
        Dispatcher $events,
        GroupInterface $group,
        GroupForm $groupForm,
        PermissionInterface $permission
    ) {
        $this->sentry = $sentry;
        $this->events = $events;
        $this->group = $group;
        $this->groupForm = $groupForm;
        $this->permission = $permission;

        // Establish Filters
        $this->beforeFilter('auth');
        $this->beforeFilter('csrf', ['on' => 'post']);
        $this->beforeFilter('hasGroupAccess:group_view', ['only' => ['show', 'index']]);
        $this->beforeFilter('hasGroupAccess:group_edit', ['only' => ['edit', 'update']]);
        $this->beforeFilter('hasGroupAccess:group_delete', ['only' => ['destroy']]);
        $this->beforeFilter('hasGroupAccess:group_create', ['only' => ['create']]);
    }

    /**
     * Display groups.
     *
     * @return Response
     */
    public function index()
    {
        // Find the user and retrieve groups
        $user = $this->sentry->getUser();
        $isSuperUser = $user->isSuperUser();
        $groups = $isSuperUser ? $this->sentry->findAllGroups() : $user->getGroups();

        foreach ($groups as $key => $group) {
            if (in_array($group->id, [1, 2]) && ! $isSuperUser) {
                unset($groups[$key]);
            }
        }

        return View::make('groups.index', compact('groups', 'user', 'isSuperUser'));
    }

    /**
     * Show create group form.
     *
     * @return Response
     */
    public function create()
    {
        $user = $this->sentry->getUser();

        return View::make('groups.create', compact('user'));
    }

    /**
     * Store a newly created group.
     *
     * @return Response
     */
    public function store()
    {
        // Form Processing
        $result = $this->groupForm->save(Input::all());

        if ($result['success']) {
            $user = $this->sentry->getUser();
            // Assign the group to the user
            if ($user->addGroup($result['group'])) {
                $this->events->fire('group.created');

                // Success!
                Session::flash('success', $result['message']);

                return Redirect::action('GroupsController@index');
            } else {
                Session::flash('error', 'groups.useradderror');

                return Redirect::action('GroupsController@create')
                    ->withInput()
                    ->withErrors($this->groupForm->errors());
            }
        } else {
            Session::flash('error', $result['message']);

            return Redirect::action('GroupsController@create')
                ->withInput()
                ->withErrors($this->groupForm->errors());
        }
    }

    /**
     * Show group.
     *
     * @param $id
     * @return $this
     */
    public function show($id)
    {
        // Get all available permissions
        $permissions = $this->permission->all();

        //Show a group and its permissions.
        $group = $this->sentry->findGroupById($id);

        $viewPermissions = $this->sentry->getUser()->hasAccess('permission_view');

        return View::make('groups.show')->with([
            'group'           => $group,
            'permissions'     => $permissions,
            'viewPermissions' => $viewPermissions
        ]);
    }

    /**
     * Show group edit form.
     *
     * @param $id
     * @return $this
     */
    public function edit($id)
    {
        // Get all available permissions
        $permissions = $this->permission->getPermissionsGroupBy();

        $editPermissions = $this->sentry->getUser()->hasAccess('permission_edit');

        $group = $this->sentry->findGroupById($id);

        return View::make('groups.edit')->with([
            'group'           => $group,
            'permissions'     => $permissions,
            'editPermissions' => $editPermissions
        ]);
    }

    /**
     * Update group.
     *
     * @param $id
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update($id)
    {
        // Form Processing
        $result = $this->groupForm->update(Input::all());

        if ($result['success']) {
            $this->events->fire('group.updated', ['groupId' => $id]);

            // Success!
            Session::flash('success', $result['message']);

            return Redirect::action('GroupsController@index');
        } else {
            Session::flash('error', $result['message']);

            return Redirect::action('GroupsController@create')
                ->withInput()
                ->withErrors($this->groupForm->errors());
        }
    }

    /**
     * Remove group.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $group = $this->sentry->findGroupById($id);
            $group->delete();
            Session::flash('success', trans('groups.group_destroyed'));

            return Redirect::action('GroupsController@index');
        } catch (GroupNotFoundException $e) {
            Session::flash('error', trans('groups.group_destroyed_failed'));

            return Redirect::action('GroupsController@index');
        }
    }
}
