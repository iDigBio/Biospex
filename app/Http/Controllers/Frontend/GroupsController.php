<?php

namespace App\Http\Controllers\Frontend;

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

        $data = [
            'user_id' => $user->id,
            'name'    => $request->get('name'),
            'label'   => $request->get('name')
        ];

        $group = $this->group->create($data);

        if ($group) {
            $user->assignGroup($group);
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

        if ($user->cannot('read', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        return view('frontend.groups.show', compact('group'));

    }

    /**
     * Show group edit form..
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        $user = Request::user();
        $group = $this->group->with(['users'])->find($id);
        $users = $group->users->toArray();

        if ($user->cannot('update', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        return view('frontend.groups.edit', compact('group', 'users'));
    }

    /**
     * Update group
     * @param GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(GroupFormRequest $request)
    {
        $user = Request::user();
        $group = $this->group->find($request->get('id'));

        if ($user->cannot('update', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        $group->name = $group->name === 'admins' ? $group->name : $request->get('name');
        $group->label = $group->label === 'Admins' ? $group->label : $request->get('name');
        
        $this->group->update($group->toArray(), $group->id);

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
        
        session_flash_push('success', trans('groups.group_destroyed'));

        return redirect()->route('web.groups.index');
    }
}
