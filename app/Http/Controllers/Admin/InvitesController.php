<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\User;
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
     * @param $groupId
     * @return \Illuminate\View\View
     */
    public function index($groupId)
    {
        $group = $this->groupContract->findWith($groupId, ['invites']);

        $error = ! $this->checkPermissions('isOwner', $group) ? true : false;
        $inviteCount = old('entries', $group->invites->count() ?: 1);

        return view('admin.partials.invite-modal-body', compact('group', 'inviteCount', 'error'));
    }

    /**
     * Send invites to emails
     *
     * @param InviteFormRequest $request
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(InviteFormRequest $request, $groupId)
    {
        $group = $this->groupContract->findWith($groupId, ['invites']);

        $this->inviteService->storeInvites($group->id, $request);

        return redirect()->back();
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

        if ( ! $this->checkPermissions('isOwner', $group))
        {
            return redirect()->route('webauth.groups.show', [$groupId]);
        }

        $this->inviteService->resendInvite($group, $inviteId);

        return redirect()->route('webauth.invites.index', [$group->id]);
    }
}
