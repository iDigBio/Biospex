<?php
/**
 * InvitesController.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Biospex\Repo\Invite\InviteInterface;
use Biospex\Form\Invite\InviteForm;
use Biospex\Mailer\BiospexMailer;
use Biospex\Helpers\Helpers;
use Cartalyst\Sentry\Users\UserNotFoundException;

class InvitesController extends BaseController {
    /**
     * Instantiate a new ProjectsController
     */
    public function __construct(
        InviteInterface $invite,
        InviteForm $inviteForm,
        BiospexMailer $mailer
    )
    {
        $this->invite = $invite;
        $this->inviteForm = $inviteForm;
        $this->mailer = $mailer;

        // Establish Filters
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('guest', array('only' => array('all')));
        $this->beforeFilter('hasGroupAccess:group_view', array('only' => array('show', 'index')));
        $this->beforeFilter('hasGroupAccess:group_edit', array('only' => array('edit', 'update')));
        $this->beforeFilter('hasGroupAccess:group_delete', array('only' => array('destroy')));
        $this->beforeFilter('hasGroupAccess:group_create', array('only' => array('create')));
    }

    /**
     * Show invite form
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        $group = Sentry::findGroupById($id);
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
        $group = Sentry::findGroupById($id);

        $emails = explode(',', Input::get('emails'));

        foreach ($emails as $email)
        {
            if ($duplicate = $this->invite->checkDuplicate($group->id, $email))
            {
                Helpers::sessionFlashPush('info', trans('groups.invite_duplicate', ['group' => $group->name, 'email' => $email]));
                continue;
            }

            try
            {
                $user = Sentry::findUserByLogin($email);
                $user->addGroup($group);
                Helpers::sessionFlashPush('success', trans('groups.user_added', ['email' => $email]));
            }
            catch (UserNotFoundException $e)
            {
                $code = str_random(10);
                $data = array(
                    'group_id' => $id,
                    'email' => trim($email),
                    'code' => $code
                );

                if (!$result = $this->inviteForm->save($data))
                {
                    Helpers::sessionFlashPush('warning', trans('groups.send_invite_error', ['group' => $group->name, 'email' => $email]));
                }
                else
                {
                    $subject = trans('emails.group_invite_subject');
                    $data = array('group' => $group->name, 'code' => $code);
                    $view = 'emails.group-invite';
                    $this->mailer->sendInvite($email, $subject, $view, $data);
                    Helpers::sessionFlashPush('success', trans('groups.send_invite_success', ['group' => $group->name, 'email' => $email]));
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
        $group = Sentry::findGroupById($groupId);

        if ($invite)
        {
            $subject = trans('emails.group_invite_subject');
            $data = array('group' => $group->name, 'code' => $invite->code);
            $view = 'emails.group-invite';
            $this->mailer->sendInvite($invite->email, $subject, $view, $data);

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
            Event::fire('invite.destroyed', array(
                'inviteId' => $inviteId,
            ));

            Session::flash('success', trans('groups.invite_destroyed'));
        }
        else
        {
            Session::flash('error', trans('groups.invite_destroyed_failed'));
        }

        return Redirect::action('groups.invites.index', [$groupId]);
    }
}