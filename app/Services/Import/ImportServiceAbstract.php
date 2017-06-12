<?php

namespace App\Services\Import;

use Illuminate\Validation\Factory as Validation;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Filesystem\Filesystem;
use App\Repositories\Contracts\ImportContract;

abstract class ImportServiceAbstract
{
    /**
     * @var ImportContract
     */
    protected $importContract;

    /**
     * Directory for storing imported files.
     *
     * @var string
     */
    protected $directory;

    /**
     * @var
     */
    protected $tube;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Validation
     */
    protected $validation;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * ImportServiceAbstract constructor.
     *
     * @param ImportContract $importContract
     * @param Filesystem $filesystem
     * @param Validation $validation
     * @param Queue $queue
     */
    public function __construct(
        ImportContract $importContract,
        Filesystem $filesystem,
        Validation $validation,
        Queue $queue
    ) {
        $this->importContract = $importContract;
        $this->filesystem = $filesystem;
        $this->validation = $validation;
        $this->queue = $queue;
    }

    /**
     * Import function.
     *
     * @param $id
     * @return mixed
     */
    abstract public function import($id);

    /**
     * Set import directory.
     *
     * @param $dir
     */
    protected function setDirectory($dir)
    {
        $this->directory = config($dir);
        if ( ! $this->filesystem->isDirectory($this->directory)) {
            $this->filesystem->makeDirectory($this->directory);
        }
    }

    /**
     * Set queue.
     *
     * @param $queue
     */
    protected function setTube($queue)
    {
        $this->tube = config($queue);
    }

    /**
     * Move uploaded file.
     *
     * @param $name
     * @return mixed
     */
    protected function moveFile($name)
    {
        $file = request()->file($name);
        $filename = md5($file->getClientOriginalName()) . '.' . $file->guessExtension();
        request()->file($name)->move($this->directory, $filename);

        return $filename;
    }

    /**
     * Insert record into import table.
     *
     * @param $user_id
     * @param $id
     * @param $filename
     * @return mixed
     */
    protected function importInsert($user_id, $id, $filename)
    {
        $import = $this->importContract->create([
            'user_id'    => $user_id,
            'project_id' => $id,
            'file'       => $filename
        ]);

        return $import;
    }
}
