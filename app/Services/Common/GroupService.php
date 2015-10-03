<?php

namespace App\Services\Common;

use App\Repositories\Contracts\Permission;
use Cartalyst\Sentry\Groups\GroupExistsException;
use Cartalyst\Sentry\Groups\GroupNotFoundException;
use Cartalyst\Sentry\Groups\NameRequiredException;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Cartalyst\Sentry\Sentry;
use App\Repositories\Contracts\Group;

class GroupService
{
    /**
     * @var Sentry
     */
    private $sentry;
    /**
     * @var Group
     */
    private $group;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Router
     */
    private $router;

    /**
     * @param Request $request
     * @param Router $router
     * @param Sentry $sentry
     * @param Group $group
     * @param Permission $permission
     */
    public function __construct(
        Request $request,
        Router $router,
        Sentry $sentry,
        Group $group,
        Permission $permission
    ) {

        $this->sentry = $sentry;
        $this->group = $group;
        $this->permission = $permission;
        $this->request = $request;
        $this->router = $router;
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
        $groups = $isSuperUser ? $this->group->all() : $user->getGroups();

        foreach ($groups as $key => $group) {
            if (in_array($group->id, [1, 2]) && ! $isSuperUser) {
                unset($groups[$key]);
            }
        }

        return compact('groups', 'user', 'isSuperUser');
    }

    /**
     * Show form to create new group form.
     *
     * @return array
     */
    public function showForm()
    {
        $user = $this->sentry->getUser();

        return compact('user');
    }

    /**
     * Store created group.
     *
     * @param $request
     * @return bool
     */
    public function store($request)
    {
        try {
            $user = $this->sentry->getUser();
            $group = $this->sentry->createGroup([
                'user_id'     => $user->id,
                'name'        => e($request->get('name')),
                'permissions' => [],
            ]);

            if ($user->addGroup($group)) {
                session_flash_push('success', trans('groups.created'));

                return true;
            }

            session_flash_push('error', 'groups.useradderror');

            return false;

        } catch (LoginRequiredException $e) {
            session_flash_push('warning', trans('groups.loginreq'));

            return false;
        }
    }

    /**
     * Show view page for group.
     *
     * @return array
     */
    public function show()
    {
        $permissions = $this->permission->all();
        $group = $this->group->findWith($this->router->input('groups'), ['users']);
        $viewPermissions = $this->sentry->getUser()->hasAccess('permission_view');

        return compact('permissions', 'group', 'viewPermissions');
    }

    /**
     * Show edit group form.
     *
     * @return array
     */
    public function edit()
    {
        $id = $this->router->input('groups');
        $group = $this->sentry->findGroupById($id);

        return compact('group');
    }

    /**
     * Update Group.
     *
     * @param $request
     * @return bool
     */
    public function update($request)
    {
        try {
            $group = $this->sentry->findGroupById($request->get('id'));
            $group->name = e($request->get('name'));
            $group->save();

            session_flash_push('success', trans('groups.updated'));

            return true;

        } catch (NameRequiredException $e) {
            session_flash_push('success', trans('groups.namereq'));

            return false;

        } catch (GroupExistsException $e) {
            session_flash_push('warning', trans('groups.groupexists'));

            return false;

        } catch (GroupNotFoundException $e) {
            session_flash_push('warning', trans('groups.notfound'));

            return false;
        }
    }

    /**
     * Delete group.
     *
     * @return bool
     */
    public function destroy()
    {
        $id = $this->router->input('groups');
        $group = $this->sentry->findGroupById($id);

        if ($group->delete()) {
            session_flash_push('success', trans('groups.group_destroyed'));

            return true;
        }

        session_flash_push('error', trans('groups.group_destroyed_failed'));

        return false;
    }

}