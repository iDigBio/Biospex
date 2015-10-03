<?php namespace App\Services\Report;

use Illuminate\Support\MessageBag;
use App\Repositories\Contracts\Group;
use App\Services\Mailer\BiospexMailer;
use League\Csv\Writer;

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
     * Constructor
     *
     * @param MessageBag $messages
     * @param Group $group
     * @param BiospexMailer $mailer
     */
    public function __construct(
        MessageBag $messages,
        Group $group,
        BiospexMailer $mailer
    ) {
        $this->messages = $messages;
        $this->group = $group;
        $this->mailer = $mailer;

        $this->exportReportsDir = \Config::get('config.export_reports_dir');
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

        if (! is_null($groupId)) {
            $group = $this->group->findWith($groupId, ['owner']);
            $email = $group->Owner->email;
        }

        $errorMessage = '';
        $messages = $this->messages->get('error');
        foreach ($messages as $message) {
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
        $path = $this->exportReportsDir;
        if (! \File::isDirectory($path)) {
            \File::makeDirectory($path);
        }

        $fileName = (is_null($name)) ? str_random(10) : $name . str_random(5);
        $ext = ".csv";

        $header = array_keys($csv[0]);
        $writer = Writer::createFromPath(new \SplFileObject(storage_path($path . "/" . $fileName . $ext), 'a+'), 'w');
        $writer->insertOne($header);
        $writer->insertAll($csv);

        return [$path . "/" . $fileName . $ext];
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
