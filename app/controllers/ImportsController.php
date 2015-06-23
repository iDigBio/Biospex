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
use Biospex\Services\Import\ImportServiceFactory;
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
     * @var ImportServiceFactory
     */
    protected $importFactory;


    /**
     * Instantiate a new ProjectsController.
     *
     * @param ImportServiceFactory $importFactory
     * @param ProjectInterface $project
     * @param Sentry $sentry
     */
    public function __construct(
        ImportServiceFactory $importFactory,
        ProjectInterface $project,
        Sentry $sentry
    )
    {
        $this->project = $project;
        $this->sentry = $sentry;
        $this->importFactory = $importFactory;

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
        $obj = $this->importFactory->create(Input::get('class'));
        if ( ! $obj)
        {
            Session::flash('error', trans('pages.bad_type'));
            return Redirect::route('projects.import', [$id]);
        }

        $validate = $obj->import($id);

        if ( ! empty($validate))
            return Redirect::route('projects.import', [$id])->withErrors($validate);

        Session::flash('success', trans('pages.upload_trans_success'));
        return Redirect::route('projects.show', [$id]);
    }
}
