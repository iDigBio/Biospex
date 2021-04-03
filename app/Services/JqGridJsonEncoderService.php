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

namespace App\Services;

use App\Services\Model\RapidHeaderModelService;
use App\Services\Model\RapidRecordModelService;
use Exception;

/**
 * Class JqGridJsonEncoderService
 *
 * @package App\Services
 */
class JqGridJsonEncoderService extends RapidServiceBase
{
    /**
     * @var \App\Services\Model\RapidRecordModelService
     */
    private $rapidRecordModelService;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $defaultGridVisible;

    /**
     * @var \App\Services\Model\RapidHeaderModelService
     */
    private $rapidHeaderModelService;

    /**
     * JqGridJsonEncoder constructor.
     *
     * @param \App\Services\Model\RapidRecordModelService $rapidRecordModelService
     * @param \App\Services\Model\RapidHeaderModelService $rapidHeaderModelService
     */
    public function __construct(
        RapidRecordModelService $rapidRecordModelService, RapidHeaderModelService $rapidHeaderModelService
    ) {
        $this->rapidRecordModelService = $rapidRecordModelService;
        $this->rapidHeaderModelService = $rapidHeaderModelService;
    }

    /**
     * Return column names and model.
     *
     * @return false|string
     */
    public function loadGridModel()
    {
        $header = $this->rapidHeaderModelService->getLatestHeader();
        if ($header === null) {
            return json_encode([]);
        }
        $headerArray = $header->data;
        array_unshift($headerArray, '_id');
        $this->defaultGridVisible = $this->getDefaultGridView();

        $data = [
            'colNames' => $headerArray,
            'colModel' => $this->setColModel($headerArray),
        ];

        return json_encode($data);
    }

    /**
     * Set column names.
     *
     * @param $fields
     * @return array
     */
    public function setColNames($fields)
    {
        $names = [];
        foreach ($fields as $field) {
            $names[] = 'occurrence.'.$field;
        }

        return $names;
    }

    /**
     * Build column model for grid.
     *
     * @param $colNames
     * @return array
     */
    protected function setColModel($colNames): array
    {
        $colModel = [];
        foreach ($colNames as $column) {
            $colModel[] = $this->formatGridColumn($column);
        }

        return $colModel;
    }

    /**
     * Format the given column for grid model.
     *
     * @param $column
     * @return array
     */
    protected function formatGridColumn($column): array
    {
        $col = $this->setNormalColumnProperties($column);

        if ($column === '_id')
        {
            $col = $this->addUriLink($col);
        }

        return $col;
    }

    /**
     * Add uri link.
     * @param $col
     * @return array
     */
    protected function addUriLink($col): array
    {
        return array_merge($col, [
            'formatter' => 'link'
        ]);

    }

    /**
     * Set normal column properties needed for grid.
     *
     * @param $column
     * @return array
     */
    protected function setNormalColumnProperties($column): array
    {
        return [
            'name'          => $column,
            'index'         => $column,
            'key'           => false,
            'resizable'     => true,
            'search'        => true,
            'sortable'      => true,
            'editable'      => false,
            'hidden'        => in_array($column, $this->defaultGridVisible) ? false : true,
            'searchoptions' => [
                'sopt'  => [
                    'eq',
                    'ne',
                    'bw',
                    'bn',
                    'ew',
                    'en',
                    'cn',
                    'nc',
                    'nu',
                    'nn',
                ],
                'value' => ':Any;true:Yes;false:No',
            ],
        ];
    }

    /**
     * Echo in a jqGrid compatible format the data requested by a grid.
     *
     * @param $postedData
     * @return array
     * @throws Exception
     */
    public function encodeGridRequestedData($postedData): array
    {
        $vars = [
            'page'         => isset($postedData['page']) ? (int) $postedData['page'] : 1,
            'limit'        => isset($postedData['rows']) ? (int) $postedData['rows'] : 25,
            'count'   => null,
            'offset'  => null,
            'sidx'         => isset($postedData['sidx']) ? $postedData['sidx'] : '_id',
            'sord'         => isset($postedData['sord']) ? $postedData['sord'] : 'desc',
            'filters' => $this->setFilters($postedData),
        ];

        $vars['count'] = $this->rapidRecordModelService->getTotalRowCount($vars);

        if (! is_int($vars['count'])) {
            throw new Exception('The method getTotalNumberOfRows must return an integer');
        }

        $vars['total'] = $vars['count'] > 0 ? ceil($vars['count']/$vars['limit']) : 0;

        $vars['page'] = $vars['page'] > $vars['total'] ? $vars['total'] : $vars['page'];

        $vars['offset'] = ($vars['limit'] * $vars['page']) - $vars['limit']; // do not put $limit*($page - 1)

        $rows = $this->rapidRecordModelService->getRows($vars);

        if (! is_array($rows) || (isset($rows[0]) && ! is_array($rows[0]))) {
            throw new Exception('The method getRows must return an array of arrays, example: array(array("column1"  =>  "1-1", "column2" => "1-2"), array("column1" => "2-1", "column2" => "2-2"))');
        }

        return [
            'page'    => $vars['page'],
            'total'   => $vars['total'],
            'records' => $vars['count'],
            'rows'    => $rows,
        ];
    }

    /**
     * @param $postedData
     * @return int
     */
    public function setPage($postedData): int
    {
        return isset($postedData['page']) ? $postedData['page'] : 1;
    }

    /**
     * Set limit.
     *
     * @param $postedData
     * @return string|null
     */
    public function setLimit($postedData): ?string
    {
        return isset($postedData['rows']) ? $postedData['rows'] : null;
    }

    /**
     * Set id.
     *
     * @param $postedData
     * @return string|null
     */
    public function setSidx($postedData): ?string
    {
        $sidx = isset($postedData['sidx']) ? $postedData['sidx'] : null;

        return (! $sidx || empty($sidx)) ? null : $sidx;
    }

    /**
     * Set order.
     *
     * @param $postedData
     * @return string|null
     */
    public function setSord($postedData): ?string
    {
        $sord = isset($postedData['sord']) ? $postedData['sord'] : null;

        return (! $sord || empty($sord)) ? null : $sord;
    }

    /**
     * Set filters.
     *
     * @param $postedData
     * @return array
     */
    public function setFilters($postedData): array
    {
        return (isset($postedData['filters']) && ! empty($postedData['filters'])) ? json_decode(str_replace('\'', '"', $postedData['filters']), true) : [];
    }

    /**
     * set total pages.
     *
     * @param $count
     * @param $limit
     * @return float|int
     */
    public function setTotalPages($count, $limit)
    {
        if ($count > 0) {
            return ceil($count / $limit);
        } else {
            return 0;
        }
    }
}