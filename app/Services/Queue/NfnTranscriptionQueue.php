<?php  namespace App\Services\Queue;

use App\Exceptions\BiospexException;
use App\Jobs\AmChartJob;
use App\Jobs\ExpeditionStatJob;
use App\Repositories\Contracts\Import;
use App\Services\Report\TranscriptionImportReport;
use App\Services\Process\NfnTranscriptionProcess;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;
use App\Exceptions\Handler;

class NfnTranscriptionQueue extends QueueAbstract
{
    use DispatchesJobs;
    
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Import
     */
    protected $import;

    /**
     * @var NfnTranscriptionProcess
     */
    protected $transcription;

    /**
     * @var TranscriptionImportReport
     */
    protected $report;

    /**
     * CSV array for collection unprocessed transcriptions.
     *
     * @var array
     */
    protected $csv = [];

    /**
     * Directory where transcriptions files are stored.
     *
     * @var string
     */
    protected $transcriptionImportDir;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem
     * @param Import $import
     * @param TranscriptionImportReport $report
     * @param NfnTranscriptionProcess $transcription
     * @param Handler $handler
     */
    public function __construct(
        Filesystem $filesystem,
        Import $import,
        TranscriptionImportReport $report,
        NfnTranscriptionProcess $transcription,
        Handler $handler
    ) {
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->report = $report;
        $this->transcription = $transcription;
        $this->handler = $handler;

        $this->transcriptionImportDir = Config::get('config.transcription_import_dir');
        if (! $this->filesystem->isDirectory($this->transcriptionImportDir)) {
            $this->filesystem->makeDirectory($this->transcriptionImportDir);
        }
    }

    /**
     * Fire method
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $import = $this->import->with(['project', 'user'])->find($this->data['id']);
        $file = $this->transcriptionImportDir . '/' . $import->file;

        try {
            $csv = $this->transcription->process($file);
            $expeditionId = $this->transcription->getExpeditionId();
            
            $this->dispatch((new ExpeditionStatJob($expeditionId))->onQueue(Config::get('config.beanstalkd.job')));
            $this->dispatch((new AmChartJob($import->project_id))->onQueue(Config::get('config.beanstalkd.job')));

            $this->report->complete($import->user->email, $import->project->title, $csv);
            $this->filesystem->delete($file);
            $this->import->delete($import->id);

        } catch (BiospexException $e) {
            $import->error = 1;
            $this->import->update($import->toArray(), $import->id);

            $this->report->addError(trans('errors.import_process', [
                'title'   => $import->project->title,
                'id'      => $import->project->id,
                'message' => $e->getMessage()
            ]));

            $this->report->reportError($import->user->email);

            $this->handler->report($e);

        }

        $this->delete();
        
    }
}
