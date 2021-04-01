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

use App\Services\Model\SubjectService;
use Exception;
use Illuminate\Support\LazyCollection;

/**
 * Class GridData
 *
 * @package App\Services\Grid
 */
class GridData
{
    /**
     * @var \App\Services\Model\SubjectService
     */
    private $subjectService;

    /**
     * GridData constructor.
     *
     * @param \App\Services\Model\SubjectService $subjectService
     */
    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    /**
     * Get total rows and assign to vars count.
     *
     * @param array $vars
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
     * @param array $vars
     * @return array
     * @throws \Exception
     */
    public function getDataRows(array $vars)
    {
        return $this->subjectService->getGridRows($vars);
    }

    /**
     * Return query so chunk export can be performed.
     *
     * @param array $vars
     * @return \Illuminate\Support\LazyCollection
     */
    public function getQueryForExport(array $vars): LazyCollection
    {
        return $this->subjectService->exportGridRows($vars);
    }

    /**
     * Prefix occurrence fields.
     *
     * @param array|null $rows
     * @throws \Exception
     */
    public function prefixOccurrence(array &$rows = null)
    {
        if (! is_array($rows) || (isset($rows[0]) && !is_array($rows[0]))) {
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
     *
     * @param array $postedData
     * @param string $route
     * @param int $projectId
     * @param int|null $expeditionId
     * @return array
     */
    public function buildVariables(array $postedData, string $route, int $projectId, int $expeditionId = null): array
    {
        return [
            'page'         => isset($postedData['page']) ? (int) $postedData['page'] : 1,
            'limit'        => isset($postedData['rows']) ? (int) $postedData['rows'] : 25,
            'count'        => null,
            'offset'       => null,
            'sidx'         => isset($postedData['sidx']) ? $postedData['sidx'] : '_id',
            'sord'         => isset($postedData['sord']) ? $postedData['sord'] : 'desc',
            'filters'      => $this->setFilters($postedData),
            'route'        => $route,
            'projectId'    => $projectId,
            'expeditionId' => $expeditionId
        ];
    }

    /**
     * Set vars filter.
     *
     * @param $postedData
     * @return array
     */
    public function setFilters($postedData): array
    {
        return (isset($postedData['filters']) && ! empty($postedData['filters'])) ?
            json_decode(str_replace('\'', '"', $postedData['filters']), true) : [];
    }

    /**
     * Set paging.
     *
     * @param array $vars
     */
    public function setPaging(array &$vars)
    {
        $vars['total'] = $vars['count'] > 0 ? ceil($vars['count']/$vars['limit']) : 0;

        $vars['page'] = $vars['page'] > $vars['total'] ? $vars['total'] : $vars['page'];

        $vars['offset'] = ($vars['limit'] * $vars['page']) - $vars['limit']; // do not put $limit*($page - 1)
    }

    /**
     * Set order by.
     *
     * @param array $vars
     */
    public function setOrderBy(array &$vars)
    {
        $vars['orderBy'] = [];
        if ($vars['sidx'] !== null) {
            $orderBys = explode(',', $vars['sidx']);
            foreach ($orderBys as $order) {
                $order = trim($order);
                [$field, $sort] = array_pad(explode(' ', $order, 2), 2, $vars['sord']);
                $vars['orderBy'] [trim($field)] = trim($sort);
            }
        }
    }
}