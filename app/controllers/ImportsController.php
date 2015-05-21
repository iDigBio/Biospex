<?php
/**
 * ImportsController.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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
use Illuminate\Support\Facades\URL;
use Biospex\Services\Import\ImportService;
use Biospex\Repo\Project\ProjectInterface;

class ImportsController extends BaseController {

    /**
     * @var Biospex\Repo\Project\ProjectInterface
     */
    protected $project;

    /**
     * @var Biospex\Repo\User\UserInterface
     */
    protected $sentry;

    /**
     * @var ImportService
     */
    protected $import;


    /**
     * Instantiate a new ProjectsController
     * @param ImportService $import
     * @param ProjectInterface $project
     * @param Sentry $sentry
     */
    public function __construct(
        ImportService $import,
        ProjectInterface $project,
        Sentry $sentry
    )
    {
        $this->project = $project;
        $this->sentry = $sentry;
        $this->import = $import;

        // Establish Filters
        $this->beforeFilter('auth');
        $this->beforeFilter('csrf', ['on' => 'post']);
        $this->beforeFilter('hasProjectAccess:project_edit', ['only' => ['import', 'upload']]);
    }

    /**
     * Add data to project
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function import($id)
    {
        $project = $this->project->findWith($id, ['group']);
        $cancel = URL::previous();
        return View::make('projects.add', compact('project', 'cancel'));
    }

    /**
     * Upload data file
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload($id)
    {
        if (empty(Input::file('file')))
        {
            Session::flash('error', trans('pages.file_required'));
            return Redirect::route('projects.import', [$id]);
        }

        if ( ! is_callable([$this->import, Input::get('type')]))
        {
            Session::flash('error', trans('pages.bad_type'));
            return Redirect::route('projects.import', [$id]);
        }

        $result = call_user_func_array([$this->import, Input::get('type')], [$id]);

        if ( ! empty($result))
        {
            Session::flash('error', trans('pages.upload_error', ['error' => $result]));
            return Redirect::route('projects.import', [$id]);
        }

        Session::flash('success', trans('pages.upload_success'));
        return Redirect::route('projects.show', [$id]);
    }
}
