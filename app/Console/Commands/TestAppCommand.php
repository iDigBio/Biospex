<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\OcrQueue;
use App\Services\Process\Ocr;
use Illuminate\Console\Command;
use App\Repositories\Contracts\Subject;

class TestAppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    private $subject;
    private $ocr;
    /**
     * @var OcrQueue
     */
    private $ocrQueue;

    /**
     * Constructor
     * @param Subject $subject
     * @param Ocr $ocr
     */
    public function __construct(Subject $subject, Ocr $ocr, OcrQueue $ocrQueue)
    {
        parent::__construct();
        $this->subject = $subject;
        $this->ocr = $ocr;
        $this->ocrQueue = $ocrQueue;
    }

    /**
     * Fire queue.
     *
     * @param Mailer $mailer
     * @param Config $config
     */
    public function fire()
    {
        $subjects = $this->subject->findByProjectId(6);
        foreach($subjects as $subject)
        {
            $subject->ocr = '';
            $subject->save();
        }

        return;
    }
}
