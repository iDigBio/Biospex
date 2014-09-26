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


class BiospexMailer extends Mailer {

	/**
	 * Outline all the events this class will be listening for. 
	 * @param  [type] $events 
	 * @return void         
	 */
	public function subscribe($events)
	{
		$events->listen('user.registered', 	'Biospex\Mailer\BiospexMailer@welcome', 10);
		$events->listen('user.resend', 		'Biospex\Mailer\BiospexMailer@welcome', 10);
		$events->listen('user.forgot',      'Biospex\Mailer\BiospexMailer@forgotPassword', 10);
		$events->listen('user.newpassword', 'Biospex\Mailer\BiospexMailer@newPassword', 10);
	}

	/**
	 * Send a welcome email to a new user.
	 * @param  string $email          
	 * @param  int    $userId         
	 * @param  string $activationCode 		
	 * @return bool
	 */
	public function welcome($email, $userId, $activationCode)
	{
		$subject = trans('users.welcome');
		$view = 'emails.welcome';
		$data['userId'] = $userId;
		$data['activationCode'] = $activationCode;
		$data['email'] = $email;

		return $this->sendTo($email, $subject, $view, $data );
	}

	/**
	 * Email Password Reset info to a user.
	 * @param  string $email          
	 * @param  int    $userId         
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

		return $this->sendTo($email, $subject, $view, $data );
	}

	/**
	 * Email New Password info to user.
	 * @param  string $email          
	 * @param  int    $userId         
	 * @param  string $resetCode 		
	 * @return bool
	 */
	public function newPassword($email, $newPassword)
	{
		$subject = trans('users.new_password');
		$view = 'emails.newpassword';
		$data['newPassword'] = $newPassword;
		$data['email'] = $email;

		return $this->sendTo($email, $subject, $view, $data );
	}

    /**
     * Send report
     *
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     * @param string $attachment
     */
    public function sendReport($from, $email, $subject, $view, $data, $attachment)
    {
        return $this->sendTo($from, $email, $subject, $view, $data, $attachment);
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
        return $this->sendTo($email, $subject, $view, $data);
    }

}