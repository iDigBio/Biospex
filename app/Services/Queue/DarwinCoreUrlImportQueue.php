<?php 

namespace App\Services\Queue;

use App\Interfaces\Import;
use App\Interfaces\Project;
use App\Notifications\DarwinCoreImportError;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Queue;
use finfo;

class DarwinCoreUrlImportQueue extends QueueAbstract
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Import
     */
    protected $importContract;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $importDir;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $tube;

    /**
     * @var Project
     */
    protected $projectContract;

    /**
     * DarwinCoreUrlImportQueue constructor.
     *
     * @param Filesystem $filesystem
     * @param Import $importContract
     * @param Project $projectContract
     */
    public function __construct(
        Filesystem $filesystem,
        Import $importContract,
        Project $projectContract
    )
    {
        $this->filesystem = $filesystem;
        $this->importContract = $importContract;
        $this->projectContract = $projectContract;

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
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        try
        {
            $this->download();
        }
        catch (\Exception $e)
        {
            $project = $this->projectContract->findWith($this->data['project_id'], ['group.owner']);

            $message = trans('errors.import_process', [
                'title'   => $project->title,
                'id'      => $project->id,
                'message' => $e->getMessage()
            ]);

            $project->group->owner->notify(new DarwinCoreImportError($message, __FILE__));
        }

        $this->delete();
    }

    /**
     * Download zip file.
     *
     * @throws \Exception
     */
    public function download()
    {
        $fileName = basename($this->data['url']);
        $filePath = $this->importDir . '/' . $fileName;

        $file = file_get_contents(url_encode($this->data['url']));
        if ($file === false)
        {
            throw new \Exception(trans('errors.zip_download'));
        }

        if (!$this->checkFileType($file))
        {
            throw new \Exception(trans('errors.zip_type'));
        }

        if (file_put_contents($filePath, $file) === false)
        {
            throw new \Exception(trans('errors.save_file', [':file' => $filePath]));
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
