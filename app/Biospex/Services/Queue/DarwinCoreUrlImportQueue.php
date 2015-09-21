<?php

namespace Biospex\Services\Queue;

use Illuminate\Filesystem\Filesystem;
use Biospex\Repo\Import\ImportInterface;
use Biospex\Services\Report\Report;
use Biospex\Repo\Project\ProjectInterface;

class DarwinCoreUrlImportQueue extends QueueAbstract
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var mixed
     */
    protected $importDir;

    /**
     * @var ImportInterface
     */
    protected $import;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var mixed
     */
    protected $queue;

    /**
     * @param Filesystem $filesystem
     * @param ImportInterface $import
     * @param Report $report
     * @param ProjectInterface $project
     */
    public function __construct(
        Filesystem $filesystem,
        ImportInterface $import,
        Report $report,
        ProjectInterface $project
    ) {
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->report = $report;
        $this->project = $project;

        $this->importDir = \Config::get('config.subjectImportDir');
        if (! $this->filesystem->isDirectory($this->importDir)) {
            $this->filesystem->makeDirectory($this->importDir);
        }

        $this->queue = \Config::get('config.beanstalkd.import');
    }

    /**
     * @param $job
     * @param $data [id, user_id, url]
     * @return bool
     * @throws \Exception
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        try {
            $this->download();
        } catch (\Exception $e) {
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
        $fileName = basename($this->data['url']);
        $filePath = $this->importDir . "/" . $fileName;

        $file = file_get_contents(\Helper::url_encode($this->data['url']));
        if ($file === false) {
            throw new \Exception(trans('emails.error_zip_download'));
        }

        if (! $this->checkFileType($file)) {
            throw new \Exception(trans('emails.error_zip_type'));
        }

        if (file_put_contents($filePath, $file) === false) {
            throw new \Exception(trans('emails.error_zip_save'));
        }


        $import = $this->importInsert($fileName);

        $data = [
            'id'    => $import->id,
            'class' => 'DarwinCoreFileImportQueue'
        ];

        \Queue::push('Biospex\Services\Queue\QueueFactory', $data, $this->queue);

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
        $finfo = new \finfo(FILEINFO_MIME);
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
            'user_id'    => $this->data['user_id'],
            'project_id' => $this->data['id'],
            'file'       => $filename
        ]);

        return $import;
    }
}
