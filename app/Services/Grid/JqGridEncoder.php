<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Grid;

use Illuminate\Support\LazyCollection;

/**
 * Class JqGridEncoder
 */
class JqGridEncoder
{
    /**
     * JqGridEncoder constructor.
     */
    public function __construct(protected GridModel $gridModel, protected GridData $gridData) {}

    /**
     * Load grid model.
     *
     * @return false|string
     */
    public function loadGridModel(int $projectId, ?string $route = null)
    {
        return $this->gridModel->createGridModel($projectId, $route);
    }

    /**
     * Get grid data.
     *
     * @param  null  $expeditionId
     *
     * @throws \Exception
     */
    public function encodeGridRequestedData($postedData, $route, $projectId, $expeditionId = null): array
    {
        $vars = $this->gridData->buildVariables($postedData, $route, (int) $projectId, $expeditionId);

        $this->gridData->getTotalRows($vars);

        $this->gridData->setPaging($vars);

        $this->gridData->setOrderBy($vars);

        $rows = $this->gridData->getDataRows($vars);

        $this->gridData->prefixOccurrence($rows);

        return [
            'page' => $vars['page'],
            'total' => $vars['total'],
            'records' => $vars['count'],
            'rows' => $rows,
        ];
    }

    /**
     * Return query for processing exports.
     */
    public function encodeGridExportData(array $data): LazyCollection
    {
        $postedData = $data['postData'];
        $route = $data['route'];
        $projectId = $data['projectId'];
        $expeditionId = $data['expeditionId'];

        $vars = $this->gridData->buildVariables($postedData, $route, $projectId, $expeditionId);
        $this->gridData->setOrderBy($vars);

        return $this->gridData->getQueryForExport($vars);
    }
}
