<?php namespace Biospex\Repo\Expedition;
/**
 * ExpeditionRepository.php
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
use Biospex\Repo\Repository;
use Expedition;

class ExpeditionRepository extends Repository implements ExpeditionInterface {

    /**
     * @param Expedition $expedition
     */
    public function __construct(Expedition $expedition)
    {
        $this->model = $expedition;
    }

    /**
     * Override parent create to allow sync
     *
     * @param array $data
     * @return mixed|void
     */
    public function create(array $data)
    {
        $expedition = parent::create($data);
        $expedition->subject()->sync($data['subject_ids']);
        return $expedition;
    }

    /**
     * Find expeditions by project id
     *
     * @param $projectId
     */
    public function findByProjectId($projectId)
    {
        return $this->model->findByProjectId($projectId);
    }
}