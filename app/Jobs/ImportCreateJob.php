<?php

namespace App\Jobs;

use Illuminate\Contracts\Bus\SelfHandling;
use App\Repositories\Contracts\Import;

class ImportCreateJob extends Job implements SelfHandling
{
    public $request;
    public $import;
    public $directory;
    public $queue;

    /**
     * @param $request
     * @param Import $import
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Handle the job.
     *
     * @return bool|mixed
     */
    public function handle()
    {
        $this->request = $this->request;
        $method = $this->request->get('method');

        if (is_callable($this->$method)) {
            return call_user_func([$this, $method]);
        }

        return false;
    }

    /**
     * Import record set.
     */
    protected function recordSetImport()
    {
        $this->setQueue('config.beanstalkd.import');

        $data = [
            'id' => $this->request->get('recordset'),
            'user_id' => $this->request->get('user_id'),
            'project_id' => $this->request->get('project_id'),
            'class' => 'RecordSetImportQueue'
        ];
        \Queue::push('App\Services\Queue\QueueFactory', $data, $this->queue);

        return true;
    }

    /**
     * Import darwin core file.
     *
     * @return bool
     */
    protected function darwinCoreFileImport()
    {
        $this->setDirectory('config.subjectImportDir');

        $filename = $this->moveFile('core');
        $import = $this->importInsert($this->request->get('user_id'), $this->request->get('project_id'), $filename);
        $this->setQueue('config.beanstalkd.import');

        $data = [
            'id' => $import->id,
            'class' => 'DarwinCoreFileImportQueue'
        ];
        \Queue::push('App\Services\Queue\QueueFactory', $data, $this->queue);

        return true;
    }

    /**
     * Import darwin core file via url.
     *
     * @return bool
     */
    protected function darwinCoreUrlImport()
    {
        $this->setQueue('config.beanstalkd.import');

        $data = [
            'user_id' => $this->request->get('user_id'),
            'project_id' => $this->request->get('project_id'),
            'class' => 'DarwinCoreUriImportQueue'
        ];
        \Queue::push('App\Services\Queue\QueueFactory', $data, $this->queue);

        return true;
    }

    /**
     * Notes from Nature transcription import.
     *
     * @return bool
     */
    protected function nfnTranscriptionImport()
    {
        $this->setDirectory('config.transcription_import_dir');

        $filename = $this->moveFile('transcription');
        $import = $this->importInsert($this->request->get('project_id'), $filename);
        $this->setQueue('config.beanstalkd.import');

        \Queue::push('App\Services\Queue\QueueFactory', ['id' => $import->id, 'class' => 'NfnTranscriptionQueue'], $this->queue);

        return true;
    }

    /**
     * Set import directory.
     *
     * @param $dir
     */
    protected function setDirectory($dir)
    {
        $this->directory = config($dir);
        if (! \File::isDirectory($this->directory)) {
            \File::makeDirectory($this->directory);
        }
    }

    /**
     * Set queue.
     *
     * @param $queue
     */
    protected function setQueue($queue)
    {
        $this->queue = config($queue);

        return;
    }

    /**
     * Move uploaded file.
     *
     * @param $name
     * @return mixed
     */
    protected function moveFile($name)
    {
        $file = $this->request->file($name);
        $filename = md5($file->getClientOriginalName()) . '.' . $file->guessExtension();
        \Input::file('file')->move($this->directory, $filename);

        return $filename;
    }

    /**
     * Insert record into import table.
     *
     * @param $user_id
     * @param $project_id
     * @param $filename
     * @param Import $import
     * @return mixed
     */
    protected function importInsert($user_id, $project_id, $filename, Import $import)
    {
        $result = $import->create([
            'user_id' => $user_id,
            'project_id' => $project_id,
            'file' => $filename
        ]);

        return $result;
    }
}
