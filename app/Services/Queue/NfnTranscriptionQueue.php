<?php  namespace Biospex\Services\Queue;

use Biospex\Repositories\Contracts\Import;
use Biospex\Services\Report\TranscriptionImportReport;
use Biospex\Services\Process\NfnTranscription;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Exception;

class NfnTranscriptionQueue extends QueueAbstract
{
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

        $import = $this->import->findWith($this->data['id'], ['project', 'user']);
        $file = $this->transcriptionImportDir . '/' . $import->file;

        try {
            $csv = $this->transcription->process($file);
            $this->report->complete($import->user->email, $import->project->title, $csv);
            $this->filesystem->delete($file);
            $this->import->destroy($import->id);
        } catch (Exception $e) {
            $import->error = 1;
            $import->save();
            $this->report->addError(trans('emails.error_import_process',
                ['id' => $import->id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            ));
            $this->report->error($import->id, $import->user->email, $import->project->title);

            return;
        }

        $this->delete();

        return;
    }
}
