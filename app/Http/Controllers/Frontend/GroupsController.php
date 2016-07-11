<?php

namespace App\Http\Controllers\Frontend;

use App\Services\Common\GroupService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupFormRequest;
use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Group;

class GroupsController extends Controller
{
    /**
     * @var Group
     */
    public $group;

    /**
     * @var User
     */
    public $user;

    /**
     * GroupsController constructor.
     *
     * @param Group $group
     * @param User $user
     */
    public function __construct(Group $group, User $user)
    {
        $this->group = $group;
        $this->user = $user;
    }

    /**
     * Display groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = $this->user->with(['groups'])->find(Request::user()->id);

        return view('frontend.groups.index', compact('user'));
    }

    /**
     * Show create group form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Request::user();

        return view('frontend.groups.create', compact('user'));
    }

    /**
     * Store a newly created group.
     *
     * @param GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(GroupFormRequest $request)
    {
        $user = Request::user();

        $group = $this->group->create(['user_id' => $user->id, 'name' => $request->get('name')]);

        if ($group) {
            $user->assignGroup($group);
            
            Event::fire('group.saved');

            session_flash_push('success', trans('groups.created'));

            return redirect()->route('web.groups.index');
        }

        session_flash_push('warning', trans('groups.loginreq'));

        return redirect()->route('web.groups.create');
    }

    /**
     * Show group page.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = Request::user();
        $with = [
            'projects',
            'owner.profile',
            'users.profile'
        ];
        $group = $this->group->with($with)->find($id);

        if ($user->cannot('show', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        return view('frontend.groups.show', compact('group'));

    }

    /**
     * Show group edit form..
     *
     * @param GroupService $service
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit(GroupService $service, $id)
    {
        $user = Request::user();
        $group = $this->group->with(['owner'])->find($id);
        $users = $service->getGroupUsersSelect($group->id);

        if ($user->cannot('update', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        return view('frontend.groups.edit', compact('group', 'users'));
    }

    /**
     * Update group.
     * 
     * @param GroupFormRequest $request
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(GroupFormRequest $request, $groupId)
    {
        $user = Request::user();

        $group = $this->group->find($groupId);

        if ($user->cannot('update', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        $this->group->update($request->all(), $group->id);

        Event::fire('group.saved');

        session_flash_push('success', trans('groups.updated'));

        return redirect()->route('web.groups.index');
    }

    /**
     * Remove group.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $user = Request::user();
        $group = $this->group->find($id);

        if ($user->cannot('delete', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        $this->group->delete($group->id);

        $groups = $this->group->whereHas('users', ['user_id' => $user->id])->get();
        Request::session()->put('groups', $groups);
        Event::fire('group.deleted');
        
        session_flash_push('success', trans('groups.group_destroyed'));

        return redirect()->route('web.groups.index');
    }
}
