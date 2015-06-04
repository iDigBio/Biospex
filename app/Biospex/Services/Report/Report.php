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
use Biospex\Repo\Group\GroupInterface;
use Biospex\Mailer\BiospexMailer;
use Maatwebsite\Excel\Excel;

class Report {

    /**
     * @var \Illuminate\Support\Contracts\MessageProviderInterface
     */
    protected $messages;

    /**
     * @var \Biospex\Mailer\BiospexMailer
     */
    protected $mailer;

    /**
     * Constructor
     *
     * @param MessageProviderInterface $messages
     * @param UserInterface $user
     * @param GroupInterface $group
     * @param BiospexMailer $mailer
     * @param Excel $excel
     */
    public function __construct(
        MessageProviderInterface $messages,
        GroupInterface $group,
        BiospexMailer $mailer,
        Excel $excel
    )
    {
        $this->messages = $messages;
        $this->group = $group;
        $this->mailer = $mailer;
        $this->excel = $excel;

        $this->excelStorage = \Config::get('excel::export');
    }

    /**
     * Add error to message bag
     *
     * @param $error
     */
    public function addError($error)
    {
        $this->messages->add('error', $error);

        return;
    }

    /**
     * Report a simple error
     *
     * @param null $groupId
     */
    public function reportSimpleError($groupId = null)
    {
        $email = null;

        if ( ! is_null($groupId))
        {
            $group = $this->group->findWith($groupId, ['owner']);
            $email = $group->Owner->email;
        }

        $errorMessage = '';
        $messages = $this->messages->get('error');
        foreach ($messages as $message)
        {
            $errorMessage .= "$message ";
        }
        $subject = trans('emails.error');
        $data = ['errorMessage' => $errorMessage];
        $view = 'emails.report-simple-error';

        $this->fireEvent('user.sendreport', $email, $subject, $view, $data);

        return;
    }

    /**
     * Current process for expedition completed successfully.
     *
     * @param $groupId
     * @param $title
     * @param $csv
     * @param $name
     */
    public function processComplete($groupId, $title, $csv = null, $name = null)
    {
        $group = $this->group->findWith($groupId, ['owner']);
        $email = $group->Owner->email;

        $count = count($csv);
        $attachment = $count ? $this->createAttachment($csv, $name) : [];

        $subject = trans('emails.expedition_complete', ['expedition' => $title]);
        $data = [
            'completeMessage' => trans('emails.expedition_complete_message',
                ['expedition' => $title])
        ];
        $view = 'emails.report-process-complete';

        $this->fireEvent('user.sendreport', $email, $subject, $view, $data, $attachment);

        return;
    }

    /**
     * Create attachment.
     *
     * @param array $csv
     * @param string $name
     * @return array
     */
    public function createAttachment($csv, $name = null)
    {
        $path = $this->excelStorage['store']['path'] . "/report/";
        if ( ! \File::isDirectory($path))
            \File::makeDirectory($path);

        $fileName = (is_null($name)) ? str_random(10) : $name . str_random(5);
        $ext = ".csv";

        $this->excel->create($fileName, function ($excel) use ($csv)
        {
            $excel->sheet('page1', function ($sheet) use ($csv)
            {
                $sheet->fromArray($csv);
            });
        })->store('csv', $path);

        return [$path . $fileName . $ext];
    }

    /**
     * Fire send report event
     *
     * @param $event
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     * @param array $attachments
     */
    protected function fireEvent($event, $email, $subject, $view, $data, $attachments = [])
    {
        \Event::fire($event, [
            'email'      => $email,
            'subject'    => $subject,
            'view'       => $view,
            'data'       => $data,
            'attachment' => $attachments
        ]);

        return;
    }

}