<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Biospex\Services\Report\SubjectImportReport as Report;


class TestCommand extends Command {

    /**
     * The console command name.
     */
    protected $name = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Constructor
     */
    public function __construct(Report $report)
    {
        parent::__construct();

        $this->report = $report;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        \Event::fire('user.registered', [
            'email'          => 'biospex@gmail.com',
            'activateHtmlLink' => HTML::linkRoute('activate', 'Click Here', ['id' => 999, 'code' => urlencode('kmlgkgmg')]),
            'activateTextLink' => route('activate', ['id' => 999, 'code' => urlencode('kmlgkgmg')])
        ]);

        /*
        $data = array(
            'projectTitle' => "Test Title",
            'importMessage' => trans('emails.import_complete_message'),
        );
        $subject = trans('emails.import_complete');
        $view = 'emails.reportsubject';

        \Event::fire('user.sendreport', [
            'email'      => 'biospex@gmail.com',
            'subject'    => $subject,
            'view'       => $view,
            'data'       => $data,
            'attachment' => []
        ]);
        */

        return;
    }
}
