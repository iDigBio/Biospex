<?php namespace App\Services\Report;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\MessageBag;
use App\Repositories\Contracts\Group;
use App\Services\Mailer\BiospexMailer;
use Illuminate\Events\Dispatcher as Event;
use App\Events\SendReportEvent;
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

        if (!is_null($groupId))
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
        $view = 'frontend.emails.report-simple-error';

        $this->fireEvent($email, $subject, $view, $data);
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
        $view = 'frontend.emails.report-process-complete';

        $this->fireEvent($email, $subject, $view, $data, $attachment);
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

        $fileName = (is_null($name)) ? str_random(10) : $name . str_random(5);
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
    protected function fireEvent($email, $subject, $view, $data, $attachments = [])
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
}
