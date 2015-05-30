<?php namespace Biospex\Http\Controllers;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Illuminate\Events\Dispatcher;
use Biospex\Repositories\Contracts\Invite;
use Biospex\Form\Invite\InviteForm;
use Biospex\Services\Mailer\BiospexMailer;

class InvitesController extends Controller {
	/**
	 * @var Sentry
	 */
	protected $sentry;

	/**
	 * @var Dispatcher
	 */
	protected $events;

	/**
	 * @var Invite
	 */
	protected $invite;

	/**
	 * @var InviteForm
	 */
	protected $inviteForm;

	/**
	 * @var BiospexMailer
	 */
	protected $mailer;

	/**
	 * Instantiate a new InvitesController
	 *
	 * @param Sentry $sentry
	 * @param Dispatcher $events
	 * @param Invite $invite
	 * @param InviteForm $inviteForm
	 * @param BiospexMailer $mailer
	 */
    public function __construct(
		Sentry $sentry,
		Dispatcher $events,
        Invite $invite,
        InviteForm $inviteForm,
        BiospexMailer $mailer
    )
    {
		$this->sentry = $sentry;
		$this->events = $events;
        $this->invite = $invite;
        $this->inviteForm = $inviteForm;
        $this->mailer = $mailer;

        // Establish Filters
        $this->middleware('auth');
		$this->beforeFilter('hasGroupAccess:group_view', ['only' => ['show', 'index']]);
		$this->beforeFilter('hasGroupAccess:group_edit', ['only' => ['edit', 'update']]);
		$this->beforeFilter('hasGroupAccess:group_delete', ['only' => ['destroy']]);
		$this->beforeFilter('hasGroupAccess:group_create', ['only' => ['create']]);
    }

    /**
     * Show invite form
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
		$group = $this->sentry->findGroupById($id);
        $invites = $this->invite->findByGroupId($group->id);

        return View::make('invites.index', compact('group', 'invites'));
    }

    /**
     * Send invites to emails
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($id)
    {
		$group = $this->sentry->findGroupById($id);

        $emails = explode(',', Input::get('emails'));

        foreach ($emails as $email)
        {
			$email = trim($email);
            if ($duplicate = $this->invite->checkDuplicate($group->id, $email))
            {
                session_flash_push('info', trans('groups.invite_duplicate', ['group' => $group->name, 'email' => $email]));
                continue;
            }

            try
            {
				$user = $this->sentry->findUserByLogin($email);
                $user->addGroup($group);
                session_flash_push('success', trans('groups.user_added', ['email' => $email]));
            }
            catch (UserNotFoundException $e)
            {
				// add invite
                $code = str_random(10);
				$data = [
                    'group_id' => $id,
                    'email' => trim($email),
                    'code' => $code
				];

                if (!$result = $this->inviteForm->save($data))
                {
                    session_flash_push('warning', trans('groups.send_invite_error', ['group' => $group->name, 'email' => $email]));
                }
                else
                {
					//send invite
					$this->events->fire('user.sendinvite', [
						'email' => $email,
						'subject' => trans('emails.group_invite_subject'),
						'view' => 'emails.group-invite',
						'data' => ['group' => $group->name, 'code' => $code],
					]);

                    session_flash_push('success', trans('groups.send_invite_success', ['group' => $group->name, 'email' => $email]));
                }
            }
        }

        return Redirect::action('groups.invites.index', [$group->id]);
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
		$group = $this->sentry->findGroupById($groupId);

        if ($invite)
        {
			//send invite
			$this->events->fire('user.sendinvite', [
				'email' => $invite->email,
				'subject' => trans('emails.group_invite_subject'),
				'view' => 'emails.group-invite',
				'data' => ['group' => $group->name, 'code' => $invite->code],
			]);

            Session::flash('success', trans('groups.send_invite_success', ['group' => $group->name, 'email' => $invite->email]));
        }
        else
        {
            Session::flash('warning', trans('groups.send_invite_error', ['group' => $group->name, 'email' => $invite->email]));
        }

        return Redirect::action('groups.invites.index', [$group->id]);
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
        if ($this->invite->destroy($inviteId))
        {
			$this->events->fire('invite.destroyed', ['inviteId' => $inviteId]);

            Session::flash('success', trans('groups.invite_destroyed'));
        }
        else
        {
            Session::flash('error', trans('groups.invite_destroyed_failed'));
        }

        return Redirect::action('groups.invites.index', [$groupId]);
    }
}