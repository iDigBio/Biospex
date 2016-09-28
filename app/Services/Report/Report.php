<?php namespace App\Services\Report;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\MessageBag;
use App\Repositories\Contracts\Group;
use App\Services\Mailer\BiospexMailer;
use Illuminate\Events\Dispatcher as Event;
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
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Event
     */
    protected $event;
    
    /**
     * @var Csv
     */
    public $csv;

    /**
     * Report constructor.
     * @param Config $config
     * @param Filesystem $filesystem
     * @param MessageBag $messages
     * @param Group $group
     * @param BiospexMailer $mailer
     * @param Event $event
     * @param Csv $csv
     */
    public function __construct(
        Config $config,
        Filesystem $filesystem,
        MessageBag $messages,
        Group $group,
        BiospexMailer $mailer,
        Event $event,
        Csv $csv
    )
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->messages = $messages;
        $this->group = $group;
        $this->mailer = $mailer;
        $this->event = $event;

        $this->exportReportsDir = $this->config->get('config.export_reports_dir');
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

    /**
     * Report an error.
     *
     * @param null $email
     */
    public function reportError($email = null)
    {
        $errorMessage = '';
        $messages = $this->messages->get('error');
        foreach ($messages as $message)
        {
            $errorMessage .= "$message ";
        }
        $subject = trans('emails.error');
        $data = ['errorMessage' => $errorMessage];
        $view = 'frontend.emails.report-error';

        $this->fireErrorEvent($email, $subject, $view, $data);
    }

    /**
     * Current process for expedition completed successfully.
     *
     * @param array $vars (title, message, groupId, attachmentName)
     * @param $csv
     */
    public function processComplete($vars, $csv = null)
    {
        $group = $this->group->with(['owner'])->find((int) $vars['groupId']);
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

        $this->event->fire(new SendReportEvent($data));
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

        $this->event->fire(new SendErrorEvent($data));
    }
}
