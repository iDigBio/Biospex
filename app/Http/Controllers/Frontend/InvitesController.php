<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Interfaces\Group;
use App\Interfaces\User;
use App\Http\Requests\InviteFormRequest;
use App\Services\Model\InviteService;

class InvitesController extends Controller
{
    /**
     * @var Group
     */
    public $groupContract;

    /**
     * @var User
     */
    public $userContract;

    /**
     * @var InviteService
     */
    private $inviteService;

    /**
     * InvitesController constructor.
     *
     * @param InviteService $inviteService
     * @param Group $groupContract
     * @param User $userContract
     */
    public function __construct(
        InviteService $inviteService,
        Group $groupContract,
        User $userContract
    ) {
        $this->inviteService = $inviteService;
        $this->groupContract = $groupContract;
        $this->userContract = $userContract;
    }

    /**
     * Show invite form
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        $group = $this->groupContract->find($id);

        if ( ! $this->checkPermissions('update', $group))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        return view('frontend.invites.index', compact('group'));
    }

    /**
     * Send invites to emails
     *
     * @param InviteFormRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(InviteFormRequest $request, $id)
    {
        $group = $this->groupContract->find($id);

        if ( ! $this->checkPermissions('update', $group))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        $this->inviteService->storeInvites($group->id, $request);

        return redirect()->route('web.invites.index', [$group->id]);
    }

    /**
     * Resend a group invite
     * @param $groupId
     * @param $inviteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend($groupId, $inviteId)
    {
        $group = $this->groupContract->find($groupId);

        if ( ! $this->checkPermissions('update', $group))
        {
            return redirect()->route('web.groups.show', [$groupId]);
        }

        $this->inviteService->resendInvite($group, $inviteId);

        return redirect()->route('web.invites.index', [$group->id]);
    }

    /**
     * Delete invite
     * @param $id
     * @param $inviteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id, $inviteId)
    {
        $group = $this->groupContract->find($id);

        if ( ! $this->checkPermissions('delete', $group))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        $this->inviteService->deleteInvite($inviteId);

        return redirect()->route('web.invites.index', [$id]);
    }
}
