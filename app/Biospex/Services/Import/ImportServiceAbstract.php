<?php  namespace Biospex\Services\Import;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Biospex\Repo\Import\ImportInterface;

abstract class ImportServiceAbstract {
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
    )
    {
        $this->sentry = $sentry;
        $this->import = $import;

        $this->directory = Config::get('config.subjectsImportDir');
    }

    /**
     * Import function.
     *
     * @param $id
     * @return mixed
     */
    abstract function import($id);

    protected function setQueue($queue)
    {
        $this->queue = Config::get($queue);

        return;
    }

    /**
     * Validation on uploaded files.
     *
     * @param $file
     * @param $type
     * @return mixed
     */
    protected function validate($type)
    {
        $validator = Validator::make(
            ['file' => Input::file('file')],
            ['file' => 'required|mimes:' . $type]
        );

        return $validator->fails();
    }

    /**
     * Move uploaded file.
     *
     * @return mixed
     */
    protected function moveFile()
    {
        $file = Input::file('file');
        $filename = $file->getClientOriginalName();
        Input::file('file')->move($this->directory, $filename);

        return $filename;
    }

    /**
     * Insert record into import table.
     *
     * @param $id
     * @param $filename
     * @return mixed
     */
    protected function importInsert($id, $filename)
    {
        $user = $this->sentry->getUser();
        $import = $this->import->create([
            'user_id' => $user->id,
            'project_id' => $id,
            'file' => $filename
        ]);

        return $import;
    }
}
