<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Requests\GroupFormRequest;
use App\Http\Requests\InviteFormRequest;
use App\Jobs\InviteCreateJob;
use App\Repositories\Contracts\GroupContract;
use App\Repositories\Contracts\UserContract;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupsController extends Controller
{

    /**
     * @var UserContract
     */
    private $userContract;

    /**
     * @var GroupContract
     */
    private $groupContract;

    /**
     * @var Request
     */
    private $request;

    /**
     * GroupsController constructor.
     * @param UserContract $userContract
     * @param GroupContract $groupContract
     * @param Request $request
     */
    public function __construct(UserContract $userContract, GroupContract $groupContract, Request $request)
    {
        $this->userContract = $userContract;
        $this->groupContract = $groupContract;
        $this->request = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $groups = $this->groupContract->findAll();
        $trashed = $this->groupContract->onlyTrashed();

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
        $user = request()->user();

        $group = $this->groupContract->create(['user_id' => $user->id, 'title' => $request->get('title')]);

        if ($group) {
            $user->assignGroup($group);

            event('group.saved');

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
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $group = $this->groupContract->with(['owner.profile', 'users.profile'])->find($id);

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
        $group = $this->groupContract->find($id);

        $result = $this->groupContract->update($group->id, $request->all());

        event('group.saved');

        $result ? Toastr::success('The Group has been updated.', 'Group Update') :
            Toastr::error('The Group could not be updated.', 'Group Update');

        return redirect()->route('admin.groups.show', [$group->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ModelDeleteService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(ModelDeleteService $service, $id)
    {
        $service->deleteGroup($id) ?
            Toastr::success('The Group has been deleted.', 'Group Delete') :
            Toastr::error('The Group could not be deleted.', 'Group Delete');

        return redirect()->route('admin.groups.index');
    }

    /**
     * Forcefully delete trashed records.
     *
     * @param ModelDestroyService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ModelDestroyService $service, $id)
    {
        $service->destroyGroup($id) ?
            Toastr::success('Group has been forcefully deleted.', 'Group Destroy') :
            Toastr::error('Group could not be forcefully deleted.', 'Group Destroy');

        return redirect()->route('admin.groups.index');
    }

    /**
     * Restore deleted record.
     *
     * @param ModelRestoreService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(ModelRestoreService $service, $id)
    {
        $service->restoreGroup($id) ?
            Toastr::success('Group has been restored successfully.', 'Group Restore') :
            Toastr::error('Group could not be restored.', 'Group Restore');

        return redirect()->route('admin.groups.show', [$id]);
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
        $group = $this->groupContract->with('invites')->find($id);

        $this->dispatch(new InviteCreateJob($request, $group->id));

        Toastr::success('The User has been invited to the Group.', 'Group User Invite');

        return redirect()->route('admin.groups.show', [$group->id]);
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
        $user = $this->userContract->find($userId);
        $result = $user->detachGroup($groupId);
        event('eloquent.deleted: *');

        $result ? Toastr::success('The user has been deleted from Group.', 'Group User Delete') :
            Toastr::error('The user could not be deleted from Group.', 'Group User Delete');

        return redirect()->route('admin.groups.index');
    }
}
