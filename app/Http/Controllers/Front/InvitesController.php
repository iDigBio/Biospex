<?php namespace App\Http\Controllers\Front;

use App\Jobs\InviteCreateJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteFormRequest;
use Illuminate\Events\Dispatcher;
use App\Repositories\Contracts\Invite;
use App\Services\Mailer\BiospexMailer;

class InvitesController extends Controller
{
    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var Invite
     */
    protected $invite;

    /**
     * @var BiospexMailer
     */
    protected $mailer;

    /**
     * Instantiate a new InvitesController
     *
     * @param Dispatcher $events
     * @param Invite $invite
     * @param BiospexMailer $mailer
     */
    public function __construct(
        Dispatcher $events,
        Invite $invite,
        BiospexMailer $mailer
    ) {
        $this->events = $events;
        $this->invite = $invite;
        $this->mailer = $mailer;
    }

    /**
     * Show invite form
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        $group = \Sentry::findGroupById($id);
        $invites = $this->invite->findByGroupId($group->id);

        return view('front.invites.index', compact('group', 'invites'));
    }

    /**
     * Send invites to emails
     *
     * @param InviteFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(InviteFormRequest $request)
    {
        $id = $this->dispatch(new InviteCreateJob($request));

        return redirect()->route('groups.invites.index', [$id]);
    }

    /**
     * Resend a group invite
     *
     * @param $groupId
     * @param $inviteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend($groupId, $inviteId)
    {
        $invite = $this->invite->find($inviteId);
        $group = \Sentry::findGroupById($groupId);

        if ($invite) {
            //send invite
            $this->events->fire('user.sendinvite', [
                'email' => $invite->email,
                'subject' => trans('emails.group_invite_subject'),
                'view' => 'emails.group-invite',
                'data' => ['group' => $group->name, 'code' => $invite->code],
            ]);

            \Session::flash('success', trans('groups.send_invite_success', ['group' => $group->name, 'email' => $invite->email]));
        } else {
            \Session::flash('warning', trans('groups.send_invite_error', ['group' => $group->name, 'email' => $invite->email]));
        }

        return redirect()->route('groups.invites.index', [$group->id]);
    }

    /**
     * Destory invite
     *
     * @param $groupId
     * @param $inviteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($groupId, $inviteId)
    {
        if ($this->invite->destroy($inviteId)) {
            $this->events->fire('invite.destroyed', ['inviteId' => $inviteId]);

            \Session::flash('success', trans('groups.invite_destroyed'));
        } else {
            \Session::flash('error', trans('groups.invite_destroyed_failed'));
        }

        return redirect()->route('groups.invites.index', [$groupId]);
    }
}
