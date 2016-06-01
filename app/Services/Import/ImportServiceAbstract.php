<?php namespace App\Services\Import;

use Illuminate\Config\Repository as Config;
use Illuminate\Validation\Factory as Validation;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Filesystem\Filesystem;
use App\Repositories\Contracts\Import;
use Illuminate\Http\Request;

abstract class ImportServiceAbstract
{
    /**
     * @var Import
     */
    protected $import;

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
     * @var Config
     */
    protected $config;

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
     * @param Request $request
     * @param Import $import
     * @param Config $config
     * @param Filesystem $filesystem
     * @param Validation $validation
     * @param Queue $queue
     */
    public function __construct(
        Request $request,
        Import $import,
        Config $config,
        Filesystem $filesystem,
        Validation $validation,
        Queue $queue
    ) {
        $this->request = $request;
        $this->import = $import;
        $this->config = $config;
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
        $this->directory = $this->config->get($dir);
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
        $this->tube = $this->config->get($queue);
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
        $this->request->file($name)->move($this->directory, $filename);

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
        $import = $this->import->create([
            'user_id'    => $user_id,
            'project_id' => $id,
            'file'       => $filename
        ]);

        return $import;
    }
}
