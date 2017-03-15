<?php

namespace App\Jobs;

use App\Exceptions\BiospexException;
use App\Services\Report\Report;
use App\Services\Process\PanoptesTranscriptionProcess;
use File;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NfnClassificationsTranscriptJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * @var array
     */
    public $ids;

    /**
     * @var bool
     */
    public $dir;

    /**
     * NfnClassificationsTranscriptJob constructor.
     *
     * @param null $ids
     * @param bool $dir
     */
    public function __construct($ids = null, $dir = false)
    {
        $this->ids = $ids;
        $this->dir = $dir;
    }

    /**
     * Execute the job.
     *
     * @param PanoptesTranscriptionProcess $transcription
     * @param Report $report
     * @return void
     */
    public function handle(PanoptesTranscriptionProcess $transcription, Report $report)
    {
        if ($this->dir)
        {
            $this->readDirectory();
        }

        $csv = null;
        foreach ($this->ids as $id)
        {
            $csv = $this->processCsvFile($transcription, $report, $id);
        }

        if (null !== $transcription->getCsvError() || $report->checkErrors())
        {
            $report->addError('Panoptes Transcript Error');
            $report->reportError(null, $csv);
        }
    }

    /**
     * Process CSV file.
     *
     * @param PanoptesTranscriptionProcess $transcription
     * @param Report $report
     * @param $id
     */
    private function processCsvFile(PanoptesTranscriptionProcess $transcription, Report $report, $id)
    {
        try
        {
            $filePath = config('config.classifications_transcript') . '/' . $id . '.csv';
            if ( ! file_exists($filePath))
            {
                return;
            }

            $transcription->process($filePath);
        }
        catch (BiospexException $e)
        {
            $report->addError($e->getMessage());
        }
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $files = File::allFiles(config('config.classifications_transcript'));
        foreach ($files as $file)
        {
            $this->ids[] = basename($file, '.csv');
        }
    }
}
