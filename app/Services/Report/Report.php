<?php

namespace App\Services\Report;

use App\Repositories\Contracts\ProjectContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\MessageBag;
use App\Repositories\Contracts\GroupContract;
use App\Services\Mailer\BiospexMailer;
use App\Events\SendReportEvent;
use App\Events\SendErrorEvent;
use App\Services\Csv\Csv;

class Report
{

    /**
     * @var MessageBag
     */
    protected $messages;

    /**
     * @var BiospexMailer
     */
    protected $mailer;

    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var Csv
     */
    public $csv;

    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * Report constructor.
     *
     * @param Filesystem $filesystem
     * @param MessageBag $messages
     * @param GroupContract $groupContract
     * @param ProjectContract $projectContract
     * @param BiospexMailer $mailer
     * @param Csv $csv
     */
    public function __construct(
        Filesystem $filesystem,
        MessageBag $messages,
        GroupContract $groupContract,
        ProjectContract $projectContract,
        BiospexMailer $mailer,
        Csv $csv
    )
    {
        $this->filesystem = $filesystem;
        $this->messages = $messages;
        $this->groupContract = $groupContract;
        $this->mailer = $mailer;
        $this->projectContract = $projectContract;

        $this->exportReportsDir = config('config.export_reports_dir');
        $this->csv = $csv;
    }

    /**
     * Add error to message bag
     *
     * @param $error
     */
    public function addError($error)
    {
        $this->messages->add('error', $error);
    }

    public function checkErrors()
    {
        return count($this->messages->get('error')) > 0;
    }

    /**
     * Report an error.
     *
     * @param null $email
     * @param null $csv
     */
    public function reportError($email = null, $csv = null)
    {
        $errorMessage = null;
        $messages = $this->messages->get('error');
        foreach ($messages as $message)
        {
            $errorMessage .= $message;
        }

        $count = count($csv);
        $attachment = $count ? $this->createAttachment($csv, 'errors') : [];

        $subject = trans('emails.error');
        $data = ['errorMessage' => null === $errorMessage ? 'No Error Reported' : $errorMessage];
        $view = 'frontend.emails.report-error';

        $this->fireErrorEvent($email, $subject, $view, $data, $attachment);
    }

    /**
     * Current process for expedition completed successfully.
     *
     * @param array $vars (title, message, groupId, attachmentName)
     * @param $csv
     */
    public function processComplete($vars, $csv = null)
    {
        $group = $this->groupContract->with('owner')->find((int) $vars['groupId']);
        $email = $group->owner->email;

        $count = count($csv);
        $attachment = $count ? $this->createAttachment($csv, $vars['attachmentName']) : [];

        $subject = trans('emails.expedition_complete', ['expedition' => $vars['title']]);
        $data = [
            'completeMessage' => $vars['message']
        ];
        $view = 'frontend.emails.report-process-complete';

        $this->fireReportEvent($email, $subject, $view, $data, $attachment);
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
        $path = $this->exportReportsDir;
        if (!$this->filesystem->isDirectory($path))
        {
            $this->filesystem->makeDirectory($path);
        }

        $fileName = ($name === null) ? str_random(10) : $name . str_random(5);
        $ext = '.csv';
        
        $this->csv->writerCreateFromPath($path . '/' . $fileName . $ext);
        $this->csv->insertOne(array_keys($csv[0]));
        $this->csv->insertAll($csv);

        return [$path . '/' . $fileName . $ext];
    }

    /**
     * Fire send report event
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     * @param array $attachments
     */
    protected function fireReportEvent($email, $subject, $view, $data, array $attachments = [])
    {
        $data = [
            'email'       => $email,
            'subject'     => $subject,
            'view'        => $view,
            'data'        => $data,
            'attachments' => $attachments
        ];

        event(new SendReportEvent($data));
    }

    /**
     * Fire send error event
     * @param $email
     * @param $subject
     * @param $view
     * @param $data
     * @param array $attachments
     */
    protected function fireErrorEvent($email, $subject, $view, $data, array $attachments = [])
    {
        $data = [
            'email'       => $email,
            'subject'     => $subject,
            'view'        => $view,
            'data'        => $data,
            'attachments' => $attachments
        ];

        event(new SendErrorEvent($data));
    }
}
