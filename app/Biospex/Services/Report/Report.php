<?php namespace Biospex\Services\Report;
/**
 * Report.php
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
use Illuminate\Support\Contracts\MessageProviderInterface;
use Biospex\Repo\User\UserInterface;

class Report
{
    public function __construct(
        MessageProviderInterface $messages,
        UserInterface $user
    )
    {
        $this->messages = $messages;
        $this->user = $user;
    }

    public function reportSimpleError($userId = null)
    {
        $emails[] = $this->adminEmail;

        if ( ! is_null($userId))
        {
            $user = $this->user->find($userId);
            $emails[] = $user->email;
        }

        $subject = trans('emails.error');
        $data = array('errorMessage' => $this->messages->first('error'));
        $view = 'emails.report-simple-error';

        $this->mailer->reportImport($emails, $subject, $view, $data, $attachment);

        if ($this->messages->any())
            die();
    }
}