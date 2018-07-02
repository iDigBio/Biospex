<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Requests\GroupFormRequest;
use App\Http\Requests\InviteFormRequest;
use App\Jobs\DeleteGroup;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\User;
use App\Services\Model\InviteService;
use App\Http\Controllers\Controller;

class GroupsController extends Controller
{

    /**
     * @var Group
     */
    private $groupContract;

    /**
     * @var User
     */
    private $userContract;

    /**
     * GroupsController constructor.
     *
     * @param Group $groupContract
     * @param User $userContract
     */
    public function __construct(Group $groupContract, User $userContract)
    {
        $this->groupContract = $groupContract;
        $this->userContract = $userContract;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $groups = $this->groupContract->all();

        return view('backend.groups.index', compact('user', 'groups'));
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

        return $this->groupContract->create($user->id, $request->get('title')) ?
            redirect()->route('admin.groups.index') :
            redirect()->route('admin.groups.index');
    }

    /**
     * Display the specified resource.
     *
     * @param $groupId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($groupId)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $group = $this->groupContract->findWith($groupId, ['owner.profile', 'users.profile']);
        return view('backend.groups.show', compact('user', 'group'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param GroupFormRequest $request
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(GroupFormRequest $request, $groupId)
    {
        $group = $this->groupContract->find($groupId);

        $this->groupContract->update($request->all(), $group->id);

        return redirect()->route('admin.groups.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($groupId)
    {
        $group = $this->groupContract->findWith($groupId, ['projects.nfnWorkflows', 'workflowManagers']);

        foreach ($group->projects as $project)
        {
            if ($project->nfnWorkflows->isNotEmpty() || $project->workflowManagers->isNotEmpty())
            {
                Flash::error(trans('messages.expedition_process_exists'));
                return redirect()->route('webauth.groups.index');
            }
        }

        DeleteGroup::dispatch($group);

        event('group.deleted', $group->id);

        return redirect()->route('admin.groups.index');
    }

    /**
     * Invite or add user to a group.
     *
     * @param InviteFormRequest $request
     * @param InviteService $inviteService
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function invite(InviteFormRequest $request, InviteService $inviteService, $groupId)
    {
        $inviteService->storeInvites($groupId, $request);

        return redirect()->route('admin.groups.show', [$groupId]);
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

        $result ? Flash::success('The user has been deleted from Group.') :
            Flash::error('The user could not be deleted from Group.');

        return redirect()->route('admin.groups.index');
    }
}
