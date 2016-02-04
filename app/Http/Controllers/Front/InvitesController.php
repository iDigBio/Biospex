<?php namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher as Event;
use App\Services\Common\PermissionService;
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
     * @var PermissionService
     */
    public $permission;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var User
     */
    public $user;

    /**
     * Instantiate a new InvitesController
     *
     * @param PermissionService $permission
     * @param Request $request
     * @param Invite $invite
     * @param Group $group
     * @param User $user
     */
    public function __construct(
        PermissionService $permission,
        Request $request,
        Invite $invite,
        Group $group,
        User $user
    ) {
        $this->permission = $permission;
        $this->request = $request;
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
        $user = $this->request->user();
        $this->group->cached(false);
        $group = $this->group->findWith($id, ['invites']);

        if ( ! $this->permission->checkPermissions($user, [$group], 'update'))
        {
            return redirect()->route('groups.get.read', [$id]);
        }

        return view('front.invites.index', compact('group'));
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
        $user = $this->request->user();
        $group = $this->group->findWith($id, ['invites']);

        if ( ! $this->permission->checkPermissions($user, [$group], 'update'))
        {
            return redirect()->route('groups.get.read', [$id]);
        }

        $this->dispatch(new InviteCreateJob($request, $group));

        return redirect()->route('invites.get.index', [$group->id]);
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
        $user = $this->request->user();
        $group = $this->group->find($id);

        if ( ! $this->permission->checkPermissions($user, [$group], 'update'))
        {
            return redirect()->route('groups.get.read', [$id]);
        }

        $invite = $this->invite->find($inviteId);

        if ($invite) {
            $data = [
                'email'   => $invite->email,
                'group'  => $invite->group_id,
                'code' => $invite->code
            ];

            $dispatcher->fire(new SendInviteEvent($data));

            session_flash_push('success', trans('groups.send_invite_success', ['group' => $group->name, 'email' => $invite->email]));
        } else {
            session_flash_push('warning', trans('groups.send_invite_error', ['group' => $group->name, 'email' => $invite->email]));
        }

        return redirect()->route('invites.get.index', [$group->id]);
    }

    /**
     * Delete invite
     * @param $id
     * @param $inviteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id, $inviteId)
    {
        $user = $this->request->user();
        $group = $this->group->find($id);

        if ( ! $this->permission->checkPermissions($user, [$group], 'delete'))
        {
            return redirect()->route('groups.get.read', [$id]);
        }

        if ($this->invite->destroy($inviteId)) {
            session_flash_push('success', trans('groups.invite_destroyed'));
        } else {
            session_flash_push('error', trans('groups.invite_destroyed_failed'));
        }

        return redirect()->route('invites.get.index', [$id]);
    }
}
