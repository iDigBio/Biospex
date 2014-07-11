<?php namespace Biospex\Services\WorkFlow;
/**
 * WorkFlowAbstract.php
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
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Services\Subject\Subject;
use Biospex\Services\Report\Report;
use Biospex\Repo\Download\DownloadInterface;

abstract class WorkFlowAbstract {

    /**
     * @var array
     */
    protected $states = array();

    public function __construct(
        ExpeditionInterface $expedition,
        Filesystem $filesystem,
        Subject $subject,
        Report $report,
        DownloadInterface $download
    )
    {
        $this->expedition = $expedition;
        $this->filesystem = $filesystem;
        $this->subject = $subject;
        $this->report = $report;
        $this->download = $download;

        $this->dataDir = Config::get('config.dataDir');
        $this->dataTmp = Config::get('config.dataDir');
        $this->metaFile = Config::get('config.metaFile');

        $this->prepareStates();
        $this->setConfig();
    }

    abstract protected function prepareStates();

    abstract protected function setConfig();

    abstract public function process($id);

    abstract protected function export();

    abstract protected function getStatus();

    abstract protected function getResults();

    /**
     * Create directory
     */
    protected function createDir($dir)
    {
        if ( ! $this->filesystem->isDirectory($dir))
        {
            if ( ! $this->filesystem->makeDirectory($dir))
                return false;
        }

        return true;
    }

    /**
     * Make sure directory is writable.
     *
     * @param $dir
     * @return bool
     */
    protected function writeDir($dir)
    {
        if ( ! $this->filesystem->isWritable($dir))
        {
            if ( ! chmod($dir, 0777))
                return false;
        }

        return true;
    }

    /**
     * Save a file to destination path
     *
     * @param $path
     * @param $contents
     * @return bool
     */
    protected function saveFile($path, $contents)
    {
        if ( ! $this->filesystem->put($path, $contents))
            return false;

        return true;
    }

    /**
     * Iterate over directory and destroy
     */
    protected function destroyDir($dir)
    {
        $success = $this->filesystem->deleteDirectory($dir);

        return $success;
    }

    /**
     * Exceute shell commands
     * @param $cmd
     */
    protected function executeCommand($cmd)
    {
        shell_exec($cmd);

        return;
    }

    protected function createDownload($expeditionId, $file)
    {
        $data = array(
            'expedition_id' => $expeditionId,
            'file' => $file
        );

        $this->download->create($data);

    }
}