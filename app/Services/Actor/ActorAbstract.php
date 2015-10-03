<?php namespace App\Services\Actor;

use Illuminate\Filesystem\Filesystem;
use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\Property;
use App\Repositories\Contracts\Download;
use App\Services\Report\Report;
use App\Services\Image\Image;

abstract class ActorAbstract
{
    /**
     * Filesystem
     * @var object
     */
    protected $filesystem;

    /**
     * ExpeditionInterface
     * @var object
     */
    protected $expedition;

    /**
     * SubjectInterface
     * @var object
     */
    protected $subject;

    /**
     * @var Property
     */
    protected $property;

    /**
     * @var Download
     */
    protected $download;

    /**
     * ReportInterface
     * @var object
     */
    protected $report;

    /**
     * ImageInterface
     * @var object
     */
    protected $image;

    /**
     * Scratch directory for processing.
     *
     * @var string
     */
    protected $scratchDir;

    /**
     * Constructor
     * @param Filesystem $filesystem
     * @param Expedition $expedition
     * @param Subject $subject
     * @param Property $property
     * @param Download $download
     * @param Report $report
     * @param Image $image
     */
    public function __construct(
        Filesystem $filesystem,
        Expedition $expedition,
        Subject $subject,
        Property $property,
        Download $download,
        Report $report,
        Image $image
    ) {
        $this->filesystem = $filesystem;
        $this->expedition = $expedition;
        $this->subject = $subject;
        $this->property = $property;
        $this->download = $download;
        $this->report = $report;
        $this->image = $image;
        $this->scratchDir = \Config::get('config.scratch_dir');
    }

    /**
     * Each class needs to set properties.
     * @param $actor
     * @return mixed
     */
    abstract protected function setProperties($actor);

    /**
     * Each class has a process to handle the states.
     * @return mixed
     */
    abstract public function process();

    /**
     * Create directory.
     *
     * @param $dir
     * @return mixed
     * @throws \Exception
     */
    protected function createDir($dir)
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
    protected function writeDir($dir)
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
    protected function saveFile($path, $contents)
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
    protected function moveFile($path, $target)
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
     */
    protected function createDownload($expeditionId, $actorId, $file)
    {
        $data = [
            'expedition_id' => $expeditionId,
            'actor_id' => $actorId,
            'file' => $file
        ];

        $this->download->create($data);
    }

    /**
     * Parse header.
     *
     * @param $header
     * @return array
     */
    protected function parseHeader($header)
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
