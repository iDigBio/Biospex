<?php namespace Biospex\Repositories;
/**
 * ProjectRepository.php
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

use Biospex\Repositories\Contracts\ProjectInterface;
use Biospex\Models\Project;

class ProjectRepository extends Repository implements ProjectInterface {

    /**
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->model = $project;
    }

	/**
	 * Find by url slug
	 *
	 * @param $slug
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
    public function bySlug($slug)
    {
        return $this->model->bySlug($slug);
    }

	/**
	 * Find by uuid
	 *
	 * @param $uuid
	 * @return mixed
	 */
	public function findByUuid($uuid)
	{
		return $this->model->findByUuid($uuid);
	}

	/**
	 * Override create for relationships and building the advertise column.
	 * @param array $data
	 * @return mixed
	 */
	public function create($data = array())
	{
		$project = $this->model->create($data);
        $project->advertise = $data;
        $project->save();

		$actors = [];
		foreach ($data['actor'] as $key => $actor)
		{
			$actors[$actor] = ['order_by' => $key];
		}
		$project->actors()->attach($actors);

		return $project;
	}

	/**
	 * Override update to handle relationship
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function update($data = array())
	{
		$project = $this->find($data['id']);
        $project->advertise = $data;
		$project->fill($data)->save();

		$actors = [];
		foreach ($data['actor'] as $key => $actor)
		{
			$actors[$actor] = ['order_by' => $key];
		}
		$project->actors()->sync($actors);

		return $project;
	}
}