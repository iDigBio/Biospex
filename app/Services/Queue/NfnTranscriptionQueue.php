<?php  namespace App\Services\Queue;

use App\Jobs\UpdateExpeditionStat;
use App\Repositories\Contracts\Import;
use App\Services\Report\TranscriptionImportReport;
use App\Services\Process\NfnTranscription;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;
use Exception;

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
     * @var NfnTranscription
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
     * Constructor.
     *
     * @param Filesystem $filesystem
     * @param Import $import
     * @param TranscriptionImportReport $report
     * @param NfnTranscription $transcription
     */
    public function __construct(
        Filesystem $filesystem,
        Import $import,
        TranscriptionImportReport $report,
        NfnTranscription $transcription
    ) {
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->report = $report;
        $this->transcription = $transcription;

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
            
            $this->dispatch((new UpdateExpeditionStat($import->project_id, $expeditionId))->onQueue(Config::get('config.beanstalkd.import')));
            
            $this->report->complete($import->user->email, $import->project->title, $csv);
            $this->filesystem->delete($file);
            $this->import->delete($import->id);
        } catch (Exception $e) {
            $import->error = 1;
            $this->import->update($import->toArray(), $import->id);
            $this->report->addError(trans('emails.error_import_process',
                ['id' => $import->id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            ));
            $this->report->error($import->id, $import->user->email, $import->project->title);

            return;
        }

        $this->delete();
        
    }
}
