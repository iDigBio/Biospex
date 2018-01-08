<?php

namespace App\Jobs;

use App\Interfaces\Import;
use App\Interfaces\Project;
use App\Notifications\DarwinCoreImportError;
use finfo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DwcUriImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var
     */
    public $data;

    /**
     * @var Import
     */
    public $importContract;

    /**
     * @var Project
     */
    public $projectContract;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->onQueue(config('config.beanstalkd.import'));
    }

    /**
     * Execute the job.
     *
     * @param Import $importContract
     * @param Project $projectContract
     * @return void
     */
    public function handle(
        Import $importContract,
        Project $projectContract
    )
    {
        try
        {
            $fileName = basename($this->data['url']);
            $filePath = config('config.subject_import_dir') . '/' . $fileName;

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

            $import = $importContract->create([
                'user_id'    => $this->data['user_id'],
                'project_id' => $this->data['id'],
                'file'       => $fileName
            ]);

            DwcFileImportJob::dispatch($import);
        }
        catch (\Exception $e)
        {
            $project = $projectContract->findWith($this->data['project_id'], ['group.owner']);

            $message = trans('errors.import_process', [
                'title'   => $project->title,
                'id'      => $project->id,
                'message' => $e->getMessage()
            ]);

            $project->group->owner->notify(new DarwinCoreImportError($message, __FILE__));
        }
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
}
