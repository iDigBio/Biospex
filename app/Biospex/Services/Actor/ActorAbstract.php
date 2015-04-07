<?php namespace Biospex\Services\Actor;
/**
 * ActorAbstract.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Config;
use Illuminate\Filesystem\Filesystem;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\Property\PropertyInterface;
use Biospex\Repo\Download\DownloadInterface;
use Biospex\Services\Report\Report;
use Biospex\Services\Image\Image;

abstract class ActorAbstract {

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
     * PropertyInterface
	 * @var object
	 */
	protected $property;

	/**
     * DownloadInterface
	 * @var object
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
     * Data directory on server.
	 * @var string
	 */
	protected $dataDir;

	/**
     * Data tmp directory on server.
	 * @var string
	 */
	protected $dataTmp;

    /**
     * Constructor
     * @param Filesystem $filesystem
     * @param ExpeditionInterface $expedition
     * @param SubjectInterface $subject
     * @param PropertyInterface $property
     * @param DownloadInterface $download
     * @param Report $report
     * @param Image $image
     * @param WorkflowManagerInterface $manager
     */
    public function __construct(
		Filesystem $filesystem,
        ExpeditionInterface $expedition,
		SubjectInterface $subject,
		PropertyInterface $property,
		DownloadInterface $download,
        Report $report,
        Image $image
    )
    {
		$this->filesystem = $filesystem;
		$this->expedition = $expedition;
		$this->subject = $subject;
		$this->property = $property;
		$this->download = $download;
        $this->report = $report;
        $this->image = $image;
        $this->dataDir = Config::get('config.dataDir');
        $this->dataTmp = Config::get('config.dataTmp');
    }

    /**
     * Each class needs to set properties.
     * @param $actor
     * @return mixed
     */
	abstract protected function setProperties ($actor);

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
        if ( ! $this->filesystem->isDirectory($dir))
        {
            if ( ! $this->filesystem->makeDirectory($dir, 0775, true))
				throw new \Exception(trans('emails.error_create_dir', ['directory' => $dir]));
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
        if ( ! $this->filesystem->isWritable($dir))
        {
            if ( ! chmod($dir, 0775))
				throw new \Exception(trans('emails.error_write_dir', ['directory' => $dir]));
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
        if ( ! $this->filesystem->put($path, $contents))
			throw new \Exception(trans('emails.error_save_file', ['directory' => $path]));

		return;
    }

    /** Create download file.
     *
     * @param $expeditionId
     * @param $actorId
     * @param $file
     */
    protected function createDownload ($expeditionId, $actorId, $file)
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
