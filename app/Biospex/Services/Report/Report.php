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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Contracts\MessageProviderInterface;
use Biospex\Repo\User\UserInterface;
use Biospex\Mailer\BiospexMailer;

class Report {
    /**
     * Debug by showing output for different actions
     *
     * @var
     */
    protected $debug = true;

    /**
     * Admin email from Config
     * @var
     */
    protected $adminEmail;

    public function __construct(
        MessageProviderInterface $messages,
        UserInterface $user,
        BiospexMailer $mailer
    )
    {
        $this->messages = $messages;
        $this->user = $user;
        $this->mailer = $mailer;
        $this->adminEmail = Config::get('config.adminEmail');
    }

    public function addError($error)
    {
        $this->messages->add('error', $error);

        return;
    }

    public function reportSimpleError($userId = null, $fatal = false)
    {
        if ($this->debug)
        {
            $this->debug($this->messages->first('error'));
            return;
        }

        if (\App::environment() == 'develop')
            return;

        $emails[] = $this->adminEmail;

        if ( ! is_null($userId))
        {
            $user = $this->user->find($userId);
            $emails[] = $user->email;
        }

        $subject = trans('errors.error');
        $data = array('errorMessage' => $this->messages->first('error'));
        $view = 'emails.report-simple-error';

        $this->mailer->sendReport($emails, $subject, $view, $data);

        if ($fatal)
            die();

        return;
    }

    public function processComplete($record)
    {
        if ($this->debug)
        {
            $this->debug("Completed {$record->title}" . PHP_EOL);
            return;
        }

        if (\App::environment() == 'develop')
            return;

        $user = $this->user->find($record->project->user_id);
        $emails[] = $user->email;

        $subject = trans('emails.expedition_complete', array('expedition' => $record->title));
        $data = array(
            'completeMessage' => trans('emails.expedition_complete_message',
                array('expedition' => $record->title))
        );
        $view = 'emails.report-process-complete';

        $this->mailer->sendReport($emails, $subject, $view, $data);

        return;
    }

    public function missingImages($record, $uuids = array(), $urls = array())
    {
        if ($this->debug)
        {
            $this->debug("Missing images for {$record->title}" . PHP_EOL);
            return;
        }

        if (\App::environment() == 'develop')
            return;

        $user = $this->user->find($record->project->user_id);
        $email = $user->email;

        $subject = trans('emails.missing_images_subject');
        $data = array(
            'missingImageMessage' => trans('emails.missing_images'),
            'expeditionTitle' => $record->title,
            'missingIds' => trans('emails.missing_img_ids'),
            'missingId' => implode("<br />", $uuids),
            'missingImageUrls' => trans('emails.missing_img_urls'),
            'missingUrl' => implode("<br />", $urls)
        );
        $view = 'emails.report-missing-images';

        $this->mailer->sendReport($email, $subject, $view, $data);
    }

    public function debug($message)
    {
        echo $message . PHP_EOL;

        return;
    }
}