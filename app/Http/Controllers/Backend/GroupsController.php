<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Requests\GroupFormRequest;
use App\Http\Requests\InviteFormRequest;
use App\Jobs\InviteCreateJob;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\User;
use Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupsController extends Controller
{

    /**
     * @var User
     */
    private $user;
    /**
     * @var Group
     */
    private $group;
    /**
     * @var Request
     */
    private $request;

    /**
     * GroupsController constructor.
     * @param User $user
     * @param Group $group
     * @param Request $request
     */
    public function __construct(User $user, Group $group, Request $request)
    {
        $this->user = $user;
        $this->group = $group;
        $this->request = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = $this->user->with(['profile'])->find($this->request->user()->id);
        $groups = $this->group->all();
        $trashed = $this->group->trashed();

        return view('backend.groups.index', compact('user', 'groups', 'trashed'));
    }

    /**
     * Store a newly created group.
     *
     * @param GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(GroupFormRequest $request)
    {
        $user = $this->request->user();

        $group = $this->group->create(['user_id' => $user->id, 'name' => $request->get('name')]);

        if ($group) {
            $user->assignGroup($group);

            Event::fire('group.saved');

            Toastr::success('The Group has been created.', 'Group Create');

            return redirect()->route('admin.groups.index');
        }

        Toastr::error('The Group could not be updated.', 'Group Update');

        return redirect()->route('admin.groups.index');
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $user = $this->user->with(['profile'])->find($this->request->user()->id);
        $group = $this->group->with(['owner.profile', 'users.profile'])->find($id);

        return view('backend.groups.show', compact('user', 'group'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param GroupFormRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(GroupFormRequest $request, $id)
    {
        $group = $this->group->find($id);

        $result = $this->group->update($request->all(), $group->id);

        Event::fire('group.saved');

        $result ? Toastr::success('The Group has been updated.', 'Group Update') :
            Toastr::error('The Group could not be updated.', 'Group Update');

        return redirect()->route('admin.groups.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $result = $this->group->delete($id);
        $result ? Toastr::success('The Group has been deleted.', 'Group Delete') :
            Toastr::error('The Group could not be deleted.', 'Group Delete');

        return redirect()->route('admin.groups.index');
    }

    /**
     * Forcefully delete trashed records.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function trash($id)
    {
        $result = $this->group->forceDelete($id);

        $result ? Toastr::success('Group has been forcefully deleted.', 'Group Destroy') :
            Toastr::error('Group could not be forcefully deleted.', 'Group Destroy');

        return redirect()->route('admin.groups.index');
    }

    /**
     * Restore deleted record.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        $result = $this->group->withTrashed($id)->restore();

        $result ? Toastr::success('Group has been restored successfully.', 'Group Restore') :
            Toastr::error('Group could not be restored.', 'Group Restore');

        return redirect()->route('admin.groups.index');
    }

    /**
     * Invite or add user to a group.
     *
     * @param InviteFormRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function invite(InviteFormRequest $request, $id)
    {
        $group = $this->group->with(['invites'])->find($id);

        $this->dispatch(new InviteCreateJob($request, $group->id));

        return redirect()->route('admin.groups.index', [$group->id]);
    }

    /**
     * Remove user from group.
     *
     * @param $groupId
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser($groupId, $userId)
    {
        $user = $this->user->find($userId);
        $result = $user->detachGroup($groupId);
        Event::fire('eloquent.deleted: *');

        $result ? Toastr::success('The user has been deleted from Group.', 'Group User Delete') :
            Toastr::error('The user could not be deleted from Group.', 'Group User Delete');

        return redirect()->route('admin.groups.index');
    }
}
