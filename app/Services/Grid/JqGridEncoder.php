<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Grid;

use Illuminate\Support\LazyCollection;

/**
 * Class JqGridEncoder
 *
 * @package App\Services\Grid
 */
class JqGridEncoder
{
    /**
     * @var \App\Services\Grid\GridModel
     */
    private $gridModel;

    /**
     * @var \App\Services\Grid\GridData
     */
    private $gridData;

    /**
     * JqGridEncoder constructor.
     *
     * @param \App\Services\Grid\GridModel $gridModel
     * @param \App\Services\Grid\GridData $gridData
     */
    public function __construct(
        GridModel $gridModel,
        GridData  $gridData
    ) {
        $this->gridModel = $gridModel;
        $this->gridData = $gridData;
    }

    /**
     * Load grid model.
     *
     * @param int $projectId
     * @return false|string
     */
    public function loadGridModel(int $projectId)
    {
        return $this->gridModel->createGridModel($projectId);
    }

    /**
     * Get grid data.
     *
     * @param $postedData
     * @param $route
     * @param $projectId
     * @param null $expeditionId
     * @return array
     * @throws \Exception
     */
    public function encodeGridRequestedData($postedData, $route, $projectId, $expeditionId = null)
    {
        $vars = $this->gridData->buildVariables($postedData, $route, $projectId, $expeditionId);

        $this->gridData->getTotalRows($vars);

        $this->gridData->setLimitByCount($vars);

        $this->gridData->setPaging($vars);

        $this->gridData->setOrderBy($vars);

        $rows = $this->gridData->getDataRows($vars);

        $this->gridData->prefixOccurrence($rows);

        return [
            'page'    => $vars['page'],
            'total'   => $vars['total'],
            'records' => $vars['count'],
            'rows'    => $rows,
        ];
    }

    /**
     * Return query for processing exports.
     *
     * @param $postedData
     * @param $route
     * @param $projectId
     * @param null $expeditionId
     * @return \Illuminate\Support\LazyCollection
     */
    public function encodeGridExportData($postedData, $route, $projectId, $expeditionId = null): LazyCollection
    {
        $vars = $this->gridData->buildVariables($postedData, $route, $projectId, $expeditionId);
        $this->gridData->setOrderBy($vars);

        return $this->gridData->getQueryForExport($vars);
    }
}