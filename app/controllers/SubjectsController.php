<?php
/**
 * SubjectsController.php
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

use Biospex\Services\Grid\JqGridJsonEncoder;
use Biospex\Repo\Project\ProjectInterface;

class SubjectsController extends BaseController {

	/**
	 * @var
	 */
	protected $grid;

	/**
	 * @var
	 */
	protected $project;

	/**
	 * Constructor.
	 *
	 * @param JqGridJsonEncoder $grid
	 * @param ProjectInterface $project
	 */
	public function __construct(JqGridJsonEncoder $grid, ProjectInterface $project)
	{
		$this->grid = $grid;
		$this->project = $project;
		$this->beforeFilter('auth');
		$this->beforeFilter('csrf', ['on' => 'post']);
	}

	/**
	 * Display subject page.
	 *
	 * @param $projectId
	 * @return \Illuminate\View\View
	 */
	public function index($projectId)
	{
		$project = $this->project->find($projectId);

		return View::make('subjects.show', compact('project'));
	}

	/**
	 * Load grid model and column names
	 */
	public function load()
	{
		return $this->grid->loadGridModel();
	}

	/**
	 * Load grid data.
	 *
	 * @throws Exception
	 */
	public function show()
	{
		$this->grid->encodeRequestedData(Input::all());
	}

	/**
	 * Store selected rows to respective expeditions.
	 *
	 * @return string
	 */
	public function store()
	{
		return $this->grid->updateSelectedRows(Route::input('expeditions'), Input::all());
	}

}