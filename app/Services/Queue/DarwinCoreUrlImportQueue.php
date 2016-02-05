<?php namespace Biospex\Services\Queue;

use Biospex\Repositories\Contracts\Import;
use Biospex\Repositories\Contracts\Project;
use Biospex\Services\Report\Report;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Exception;
use finfo;

class DarwinCoreUrlImportQueue extends QueueAbstract
{
    protected $filesystem;
    protected $import;
    protected $report;
    protected $importDir;
    protected $tube;
    protected $project;

    /**
     * @param Filesystem $filesystem
     * @param Import $import
     * @param Report $report
     * @param Project $project
     */
    public function __construct(
        Filesystem $filesystem,
        Import $import,
        Report $report,
        Project $project
    ) {
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->report = $report;
        $this->project = $project;

        $this->importDir = Config::get('config.subject_import_dir');
        if (! $this->filesystem->isDirectory($this->importDir)) {
            $this->filesystem->makeDirectory($this->importDir);
        }

        $this->tube = Config::get('config.beanstalkd.import');
    }

    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        try {
            $this->download();
        } catch (Exception $e) {
            $project = $this->project->findWith($this->data['project_id'], ['group']);
            $this->report->addError(trans('emails.error_import_process',
                ['id' => $this->data['id'], 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            ));
            $this->report->reportSimpleError($project->group->id);
        }

        $this->delete();

        return;
    }

    /**
     * Download zip file.
     *
     * @return bool
     * @throws \Exception
     */
    public function download()
    {
        $fileName =  basename($this->data['url']);
        $filePath = $this->importDir . "/" .$fileName;

        $file = file_get_contents(url_encode($this->data['url']));
        if ($file === false) {
            throw new Exception(trans('emails.error_zip_download'));
        }

        if (! $this->checkFileType($file)) {
            throw new Exception(trans('emails.error_zip_type'));
        }

        if (file_put_contents($filePath, $file) === false) {
            throw new Exception(trans('emails.error_zip_save'));
        }


        $import = $this->importInsert($fileName);

        $data = [
            'id' => $import->id,
            'class' => 'DarwinCoreFileImportQueue'
        ];

        Queue::push('Biospex\Services\Queue\QueueFactory', $data, $this->tube);

        return;
    }

    /**
     * Check if file is zip.
     *
     * @param $file
     * @return bool
     */
    protected function checkFileType($file)
    {
        $finfo = new finfo(FILEINFO_MIME);
        list($mime, $char) = explode(';', $finfo->buffer($file));
        $types = ['application/zip', 'application/octet-stream'];
        if (! in_array(trim($mime), $types)) {
            return false;
        }

        return true;
    }

    /**
     * Insert record into import table.
     *
     * @param $filename
     * @return mixed
     */
    protected function importInsert($filename)
    {
        $import = $this->import->create([
            'user_id' => $this->data['user_id'],
            'project_id' => $this->data['id'],
            'file' => $filename
        ]);

        return $import;
    }
}
