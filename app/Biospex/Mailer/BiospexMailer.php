<?php namespace Biospex\Mailer;

/**
 * BiospexMailer.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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
use Illuminate\Support\Facades\Config;

class BiospexMailer extends Mailer {

    public function __construct()
    {
        $this->adminEmail = Config::get('config.adminEmail');
        $this->doNotReplyEmail = Config::get('config.doNotReplyEmail');
    }

    /**
     * Outline all the events this class will be listening for.
     * @param  [type] $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen('user.registered', 'Biospex\Mailer\BiospexMailer@welcome', 10);
        $events->listen('user.resend', 'Biospex\Mailer\BiospexMailer@welcome', 10);
        $events->listen('user.forgot', 'Biospex\Mailer\BiospexMailer@forgotPassword', 10);
        $events->listen('user.newpassword', 'Biospex\Mailer\BiospexMailer@newPassword', 10);
        $events->listen('user.sendreport', 'Biospex\Mailer\BiospexMailer@sendReport', 10);
        $events->listen('user.sendinvite', 'Biospex\Mailer\BiospexMailer@sendInvite', 10);
        $events->listen('user.sendcontact', 'Biospex\Mailer\BiospexMailer@sendContactForm', 10);
    }

    /**
     * Send a welcome email to a new user.
     * @param $email
     * @param $userId
     * @param $activateHtmlLink
     * @param $activateTextLink
     */
    public function welcome($email, $activateHtmlLink, $activateTextLink)
    {
        $subject = trans('users.welcome');
        $view = 'emails.welcome';
        $data['activateHtmlLink'] = $activateHtmlLink;
        $data['activateTextLink'] = $activateTextLink;
        $data['email'] = $email;

        return $this->sendTo($this->doNotReplyEmail, $email, $subject, $view, $data);
    }

    /**
     * Email Password Reset info to a user.
     * @param  string $email
     * @param  int $userId
     * @param  string $resetCode
     * @return bool
     */
    public function forgotPassword($email, $userId, $resetCode)
    {
        $subject = trans('users.reset_password');
        $view = 'emails.reset';
        $data['userId'] = $userId;
        $data['resetCode'] = $resetCode;
        $data['email'] = $email;

        return $this->sendTo($this->doNotReplyEmail, $email, $subject, $view, $data);
    }

    /**
     * Email New Password info to user.
     *
     * @param $email
     * @param $newPassword
     */
    public function newPassword($email, $newPassword)
    {
        $subject = trans('users.new_password');
        $view = 'emails.newpassword';
        $data['newPassword'] = $newPassword;
        $data['email'] = $email;

        return $this->sendTo($this->doNotReplyEmail, $email, $subject, $view, $data);
    }

    /**
     * Send report
     *
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     * @param array $attachments
     */
    public function sendReport($email, $subject, $view, $data, $attachments = [])
    {
        if (is_null($email))
            $email = $this->adminEmail;

        return $this->sendTo($this->doNotReplyEmail, $email, $subject, $view, $data, $attachments);
    }

    /**
     * Send invite
     *
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     * @param string $attachment
     */
    public function sendInvite($email, $subject, $view, $data)
    {
        if (is_null($email))
            $email = $this->adminEmail;

        return $this->sendTo($this->doNotReplyEmail, $email, $subject, $view, $data);
    }

    /**
     * Send contact form.
     *
     * @param $subject
     * @param $view
     * @param $data
     */
    public function sendContactForm($view, $subject, $data)
    {
        return $this->sendTo($this->doNotReplyEmail, $this->adminEmail, $subject, $view, $data);
    }

}