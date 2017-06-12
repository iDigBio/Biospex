<?php 

namespace App\Services\Queue;

use App\Exceptions\BiospexException;
use App\Exceptions\DownloadFileException;
use App\Exceptions\FileSaveException;
use App\Exceptions\FileTypeException;
use App\Repositories\Contracts\ImportContract;
use App\Repositories\Contracts\ProjectContract;
use App\Services\Report\Report;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Queue;
use finfo;
use App\Exceptions\Handler;

class DarwinCoreUrlImportQueue extends QueueAbstract
{

    protected $filesystem;
    protected $importContract;
    protected $report;
    protected $importDir;
    protected $tube;
    protected $projectContract;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * DarwinCoreUrlImportQueue constructor.
     *
     * @param Filesystem $filesystem
     * @param ImportContract $importContract
     * @param Report $report
     * @param ProjectContract $projectContract
     * @param Handler $handler
     */
    public function __construct(
        Filesystem $filesystem,
        ImportContract $importContract,
        Report $report,
        ProjectContract $projectContract,
        Handler $handler
    )
    {
        $this->filesystem = $filesystem;
        $this->importContract = $importContract;
        $this->report = $report;
        $this->projectContract = $projectContract;
        $this->handler = $handler;

        $this->importDir = config('config.subject_import_dir');
        if (!$this->filesystem->isDirectory($this->importDir))
        {
            $this->filesystem->makeDirectory($this->importDir);
        }

        $this->tube = config('config.beanstalkd.import');
    }

    /**
     * Fire the job.
     *
     * @param $job
     * @param $data
     * @throws BiospexException
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        try
        {
            $this->download();
        }
        catch (BiospexException $e)
        {
            $project = $this->projectContract->with('group.owner')->find($this->data['project_id']);

            $this->report->addError(trans('errors.import_process', [
                'title'   => $project->title,
                'id'      => $project->id,
                'message' => $e->getMessage()
            ]));

            $this->report->reportError($project->group->owner->email);

            $this->handler->report($e);
        }

        $this->delete();
    }

    /**
     * Download zip file.
     *
     * @throws BiospexException
     */
    public function download()
    {
        $fileName = basename($this->data['url']);
        $filePath = $this->importDir . '/' . $fileName;

        $file = file_get_contents(url_encode($this->data['url']));
        if ($file === false)
        {
            throw new DownloadFileException(trans('errors.zip_download'));
        }

        if (!$this->checkFileType($file))
        {
            throw new FileTypeException(trans('errors.zip_type'));
        }

        if (file_put_contents($filePath, $file) === false)
        {
            throw new FileSaveException(trans('errors.save_file', [':file' => $filePath]));
        }


        $import = $this->importInsert($fileName);

        $data = [
            'id'    => $import->id,
            'class' => 'DarwinCoreFileImportQueue'
        ];

        Queue::push('App\Services\Queue\QueueFactory', $data, $this->tube);
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
        if (!in_array(trim($mime), $types))
        {
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
        $import = $this->importContract->create([
            'user_id'    => $this->data['user_id'],
            'project_id' => $this->data['id'],
            'file'       => $filename
        ]);

        return $import;
    }
}
