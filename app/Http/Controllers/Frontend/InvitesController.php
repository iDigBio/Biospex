<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\InviteContract;
use App\Repositories\Contracts\GroupContract;
use App\Repositories\Contracts\UserContract;
use App\Http\Requests\InviteFormRequest;
use App\Jobs\InviteCreateJob;
use App\Events\SendInviteEvent;

class InvitesController extends Controller
{
    /**
     * @var InviteContract
     */
    public $inviteContract;

    /**
     * @var GroupContract
     */
    public $groupContract;

    /**
     * @var UserContract
     */
    public $userContract;

    /**
     * InvitesController constructor.
     * 
     * @param InviteContract $inviteContract
     * @param GroupContract $groupContract
     * @param UserContract $userContract
     */
    public function __construct(
        InviteContract $inviteContract,
        GroupContract $groupContract,
        UserContract $userContract
    ) {
        $this->inviteContract = $inviteContract;
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
        $user = request()->user();
        $group = $this->groupContract->setCacheLifetime(0)->with('invites')->find($id);

        if ( ! $this->checkPermissions($user, [$group], 'update'))
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
        $user = request()->user();
        $group = $this->groupContract->with('invites')->find($id);

        if ( ! $this->checkPermissions($user, [$group], 'update'))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        $this->dispatch(new InviteCreateJob($request, $group->id));

        return redirect()->route('web.invites.index', [$group->id]);
    }

    /**
     * Resend a group invite
     * @param $id
     * @param $inviteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend($id, $inviteId)
    {
        $user = request()->user();
        $group = $this->groupContract->find($id);

        if ( ! $this->checkPermissions($user, [$group], 'update'))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        $invite = $this->inviteContract->find($inviteId);

        if ($invite) {
            $data = [
                'email'   => $invite->email,
                'group'  => $invite->group_id,
                'code' => $invite->code
            ];

            event(new SendInviteEvent($data));

            session_flash_push('success', trans('groups.send_invite_success', ['group' => $group->title, 'email' => $invite->email]));
        } else {
            session_flash_push('warning', trans('groups.send_invite_error', ['group' => $group->title, 'email' => $invite->email]));
        }

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
        $user = request()->user();
        $group = $this->groupContract->find($id);

        if ( ! $this->checkPermissions($user, [$group], 'delete'))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        if ($this->inviteContract->delete($inviteId)) {
            session_flash_push('success', trans('groups.invite_destroyed'));
        } else {
            session_flash_push('error', trans('groups.invite_destroyed_failed'));
        }

        return redirect()->route('web.invites.index', [$id]);
    }
}
