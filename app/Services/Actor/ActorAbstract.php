<?php namespace Biospex\Services\Actor;

abstract class ActorAbstract
{
    /**
     * Filesystem
     *
     * @var object
     */
    protected $filesystem;

    /**
     * DownloadInterface
     *
     * @var object
     */
    protected $download;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Scratch directory for processing.
     *
     * @var string
     */
    protected $scratchDir;

    /**
     * Each class has a process to handle the states.
     *
     * @param $actor
     * @return mixed
     */
    abstract public function process($actor);

    /**
     * Create directory.
     *
     * @param $dir
     * @return mixed
     * @throws \Exception
     */
    public function createDir($dir)
    {
        if (! $this->filesystem->isDirectory($dir)) {
            if (! $this->filesystem->makeDirectory($dir, 0775, true)) {
                throw new \Exception(trans('emails.error_create_dir', ['directory' => $dir]));
            }
        }

        return $dir;
    }

    /**
     * Make sure directory is writable.
     *
     * @param $dir
     * @throws \Exception
     */
    public function writeDir($dir)
    {
        if (! $this->filesystem->isWritable($dir)) {
            if (! chmod($dir, 0775)) {
                throw new \Exception(trans('emails.error_write_dir', ['directory' => $dir]));
            }
        }

        return;
    }

    /**
     * Save a file to destination path.
     *
     * @param $path
     * @param $contents
     * @throws \Exception
     */
    public function saveFile($path, $contents)
    {
        if (! $this->filesystem->put($path, $contents)) {
            throw new \Exception(trans('emails.error_save_file', ['directory' => $path]));
        }

        return;
    }

    /**
     * Move a file.
     *
     * @param $path
     * @param $target
     * @throws \Exception
     */
    public function moveFile($path, $target)
    {
        if (! $this->filesystem->move($path, $target)) {
            throw new \Exception(trans('emails.error_save_file', ['directory' => $path]));
        }

        return;
    }

    /** Create download file.
     *
     * @param $expeditionId
     * @param $actorId
     * @param $file
     * @param null $data
     * @return
     */
    public function createDownload($expeditionId, $actorId, $file, $data = null)
    {
        $data = [
            'expedition_id' => $expeditionId,
            'actor_id'      => $actorId,
            'file'          => $file,
            'data'          => $data,
        ];

        return $this->download->create($data);
    }

    /**
     * Parse header.
     *
     * @param $header
     * @return array
     */
    public function parseHeader($header)
    {
        $headers = [];

        foreach (explode("\n", $header) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                $headers[$h[0]] = trim($h[1]);
            }
        }

        return $headers;
    }
}
