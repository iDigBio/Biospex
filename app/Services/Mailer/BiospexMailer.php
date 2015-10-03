<?php namespace App\Services\Mailer;

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

class BiospexMailer extends Mailer
{
    public function __construct()
    {
        $this->emailAddress = \Config::get('mail.from');
    }

    /**
     * Send a welcome email to a new user.
     *
     * @param $email
     * @param $activateHtmlLink
     * @param $activateTextLink
     */
    public function welcome($email, $activateHtmlLink, $activateTextLink)
    {
        $subject = trans('users.welcome');
        $view = 'emails.welcome';
        $data['activateHtmlLink'] = $activateHtmlLink;
        $data['activateTextLink'] = $activateTextLink;
        $data['adminEmail'] = $this->emailAddress;
        $data['email'] = $email;

        return $this->sendTo($this->emailAddress, $email, $subject, $view, $data);
    }

    /**
     * Email Password Reset info to a user.
     * @param $email
     * @param $resetHtmlLink
     * @param $resetTextLink
     */
    public function forgotPassword($email, $resetHtmlLink, $resetTextLink)
    {
        $subject = trans('users.reset_password');
        $view = 'emails.reset';
        $data['resetHtmlLink'] = $resetHtmlLink;
        $data['resetTextLink'] = $resetTextLink;
        $data['adminEmail'] = $this->emailAddress;
        $data['email'] = $email;

        return $this->sendTo($this->emailAddress, $email, $subject, $view, $data);
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
        $data['adminEmail'] = $this->emailAddress;
        $data['email'] = $email;

        return $this->sendTo($this->emailAddress, $email, $subject, $view, $data);
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
        if (is_null($email)) {
            $email = $this->emailAddress;
        }

        $data['adminEmail'] = $this->emailAddress;

        return $this->sendTo($this->emailAddress, $email, $subject, $view, $data, $attachments);
    }

    /**
     * Send group invite
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     */
    public function sendInvite($email, $subject, $view, $data)
    {
        if (is_null($email)) {
            $email = $this->emailAddress;
        }

        $data['adminEmail'] = $this->emailAddress;

        return $this->sendTo($this->emailAddress, $email, $subject, $view, $data);
    }

}
