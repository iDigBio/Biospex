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

use App\Services\Subject\SubjectService;
use Exception;
use Illuminate\Support\LazyCollection;

/**
 * Class GridData
 */
class GridData
{
    /**
     * GridData constructor.
     */
    public function __construct(protected SubjectService $subjectService) {}

    /**
     * Get total rows and assign to vars count.
     *
     * @throws \Exception
     */
    public function getTotalRows(array &$vars)
    {
        $vars['count'] = $this->subjectService->getGridTotalRowCount($vars);

        if (! is_int($vars['count'])) {
            throw new Exception('The method getTotalNumberOfRows must return an integer');
        }
    }

    /**
     * Get rows using set variables.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getDataRows(array $vars)
    {
        return $this->subjectService->getGridRows($vars);
    }

    /**
     * Return query so chunk export can be performed.
     */
    public function getQueryForExport(array $vars): LazyCollection
    {
        return $this->subjectService->exportGridRows($vars);
    }

    /**
     * Prefix occurrence fields.
     *
     * @throws \Exception
     */
    public function prefixOccurrence(?array &$rows = null)
    {
        if (! is_array($rows) || (isset($rows[0]) && ! is_array($rows[0]))) {
            throw new Exception('The method getGridRows must return an array of arrays, example: array(array("column1"  =>  "1-1", "column2" => "1-2"), array("column1" => "2-1", "column2" => "2-2"))');
        }

        // Prefix occurrence fields, merge into row, unset occurrence
        foreach ($rows as $key => $row) {
            $row['occurrence'] = array_combine(array_map(function ($k) {
                return 'occurrence.'.$k;
            }, array_keys($row['occurrence'])), $row['occurrence']);

            $rows[$key] = array_merge($row, $row['occurrence']);
            unset($rows[$key]['occurrence']);
        }
    }

    /**
     * build variables array for querying.
     */
    public function buildVariables(array $postedData, string $route, int $projectId, ?int $expeditionId = null): array
    {
        return [
            'page' => isset($postedData['page']) ? (int) $postedData['page'] : 1,
            'limit' => isset($postedData['rows']) ? (int) $postedData['rows'] : 25,
            'count' => null,
            'offset' => null,
            'sidx' => isset($postedData['sidx']) ? $postedData['sidx'] : '_id',
            'sord' => isset($postedData['sord']) ? $postedData['sord'] : 'desc',
            'filters' => $this->setFilters($postedData),
            'route' => $route,
            'projectId' => $projectId,
            'expeditionId' => $expeditionId,
        ];
    }

    /**
     * Set vars filter.
     */
    public function setFilters($postedData): array
    {
        return (isset($postedData['filters']) && ! empty($postedData['filters'])) ?
            json_decode(str_replace('\'', '"', $postedData['filters']), true) : [];
    }

    /**
     * Set paging.
     */
    public function setPaging(array &$vars)
    {
        $vars['total'] = $vars['count'] > 0 ? ceil($vars['count'] / $vars['limit']) : 0;

        $vars['page'] = $vars['page'] > $vars['total'] ? $vars['total'] : $vars['page'];

        $vars['offset'] = ($vars['limit'] * $vars['page']) - $vars['limit']; // do not put $limit*($page - 1)
    }

    /**
     * Set order by.
     */
    public function setOrderBy(array &$vars)
    {
        $vars['orderBy'] = [];
        if ($vars['sidx'] !== null) {
            $orderBys = explode(',', $vars['sidx']);
            foreach ($orderBys as $order) {
                $order = trim($order);
                [$field, $sort] = array_pad(explode(' ', $order, 2), 2, $vars['sord']);
                $vars['orderBy'][trim($field)] = trim($sort);
            }
        }
    }
}
