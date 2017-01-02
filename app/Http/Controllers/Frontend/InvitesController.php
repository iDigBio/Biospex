<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Events\Dispatcher as Event;
use App\Repositories\Contracts\Invite;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\User;
use App\Http\Requests\InviteFormRequest;
use App\Jobs\InviteCreateJob;
use App\Events\SendInviteEvent;

class InvitesController extends Controller
{
    /**
     * @var Invite
     */
    public $invite;

    /**
     * @var Group
     */
    public $group;

    /**
     * @var User
     */
    public $user;

    /**
     * InvitesController constructor.
     * 
     * @param Invite $invite
     * @param Group $group
     * @param User $user
     */
    public function __construct(
        Invite $invite,
        Group $group,
        User $user
    ) {
        $this->invite = $invite;
        $this->group = $group;
        $this->user = $user;
    }

    /**
     * Show invite form
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        $user = Request::user();
        $group = $this->group->skipCache()->with(['invites'])->find($id);

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
        $user = Request::user();
        $group = $this->group->with(['invites'])->find($id);

        if ( ! $this->checkPermissions($user, [$group], 'update'))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        $this->dispatch(new InviteCreateJob($request, $group->id));

        return redirect()->route('web.invites.index', [$group->id]);
    }

    /**
     * Resend a group invite
     * @param Event $dispatcher
     * @param $id
     * @param $inviteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Event $dispatcher, $id, $inviteId)
    {
        $user = Request::user();
        $group = $this->group->find($id);

        if ( ! $this->checkPermissions($user, [$group], 'update'))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        $invite = $this->invite->find($inviteId);

        if ($invite) {
            $data = [
                'email'   => $invite->email,
                'group'  => $invite->group_id,
                'code' => $invite->code
            ];

            $dispatcher->fire(new SendInviteEvent($data));

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
        $user = Request::user();
        $group = $this->group->find($id);

        if ( ! $this->checkPermissions($user, [$group], 'delete'))
        {
            return redirect()->route('web.groups.show', [$id]);
        }

        if ($this->invite->delete($inviteId)) {
            session_flash_push('success', trans('groups.invite_destroyed'));
        } else {
            session_flash_push('error', trans('groups.invite_destroyed_failed'));
        }

        return redirect()->route('web.invites.index', [$id]);
    }
}
