<?php  namespace Biospex\Services\Import;
/**
 * ImportService.php
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
use Illuminate\Support\Facades\Queue;
use Biospex\Repo\Import\ImportInterface;

class ImportService {

    /**
     * @var Sentry
     */
    protected $sentry;

    /**
     * @var ImportInterface
     */
    protected $import;

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
    }

    /**
     * Upload subjects for project.
     *
     * @param $id
     * @return string|void
     */
    public function subjects($id)
    {
        $validator = Validator::make(
            ['file' => Input::file('file')],
            ['file' => 'required|mimes:zip']
        );

        if($validator->fails())
            return trans('pages.file_type_error');

        $file = Input::file('file');
        $filename = $file->getClientOriginalName();
        $directory = Config::get('config.subjectsImportDir');

        Input::file('file')->move($directory, $filename);
        $user = $this->sentry->getUser();
        $import = $this->import->create([
            'user_id' => $user->id,
            'project_id' => $id,
            'file' => $filename
        ]);

        Queue::push('Biospex\Services\Queue\SubjectsImportService', ['id' => $import->id], \Config::get('config.beanstalkd.subjects-import'));

        return;
    }

    public function nfn($id)
    {
        $validator = Validator::make(
            ['file' => Input::file('file')],
            ['file' => 'required|mimes:csv']
        );

        if($validator->fails())
            return trans('pages.file_type_error');

        $file = Input::file('file');
        $filename = $file->getClientOriginalName();


        //Queue::push('Biospex\Services\Queue\SubjectsImportService', ['id' => $import->id], \Config::get('config.beanstalkd.subjects-import'));

        return;
    }

}