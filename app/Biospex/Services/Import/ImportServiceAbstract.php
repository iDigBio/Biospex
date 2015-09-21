<?php namespace Biospex\Services\Import;

/**
 * ImportServiceAbstract.php
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

use Cartalyst\Sentry\Sentry;
use Biospex\Repo\Import\ImportInterface;

abstract class ImportServiceAbstract
{
    /**
     * @var Sentry
     */
    protected $sentry;

    /**
     * @var ImportInterface
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
    protected $queue;

    /**
     * Instantiate a new ProjectsController.
     *
     * @param Sentry $sentry
     * @param ImportInterface $import
     */
    public function __construct(
        Sentry $sentry,
        ImportInterface $import
    ) {
        $this->sentry = $sentry;
        $this->import = $import;
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
        $this->directory = \Config::get($dir);
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
        $this->queue = \Config::get($queue);

        return;
    }

    /**
     * Move uploaded file.
     *
     * @return mixed
     */
    protected function moveFile()
    {
        $file = \Input::file('file');
        $filename = md5($file->getClientOriginalName()) . '.' . $file->guessExtension();
        \Input::file('file')->move($this->directory, $filename);

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
