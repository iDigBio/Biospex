<?php
/**
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

use App\Repositories\Interfaces\RapidRecord;
use Exception;

class JqGridJsonEncoderService
{
    /**
     * @var \App\Repositories\Interfaces\RapidRecord
     */
    private $rapidRecord;

    /**
     * @var \App\Services\RapidFileService
     */
    private $rapidFileService;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $defaultGridVisible;

    /**
     * JqGridJsonEncoder constructor.
     *
     * @param \App\Repositories\Interfaces\RapidRecord $rapidRecord
     * @param \App\Services\RapidFileService $rapidFileService
     */
    public function __construct(
        RapidRecord $rapidRecord, RapidFileService $rapidFileService
    ) {
        $this->rapidRecord = $rapidRecord;
        $this->rapidFileService = $rapidFileService;
    }

    /**
     * Load grid model.
     *
     * @return array
     */
    public function loadGridModel()
    {
        $header = $this->rapidFileService->getHeader();
        array_unshift($header, '_id');
        $this->defaultGridVisible = $this->rapidFileService->getDefaultGridView();

        return [
            'colNames' => $header,
            'colModel' => $this->setColModel($header),
        ];

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
    protected function setColModel($colNames)
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
    protected function formatGridColumn($column)
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
    protected function addUriLink($col)
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
    protected function setNormalColumnProperties($column)
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
    public function encodeGridRequestedData($postedData)
    {
        $vars = [
            'page'    => $this->setPage($postedData),
            'limit'   => $this->setLimit($postedData),
            'count'   => null,
            'offset'  => null,
            'sidx'    => $this->setSidx($postedData),
            'sord'    => $this->setSord($postedData),
            'filters' => $this->setFilters($postedData),
        ];

        $vars['count'] = $this->rapidRecord->getTotalRowCount($vars);

        $vars['limit'] = (int) $vars['limit'] === 0 ? (int) $vars['count'] : (int) $vars['limit'];

        if (! is_int($vars['count'])) {
            throw new Exception('The method getTotalNumberOfRows must return an integer');
        }

        $totalPages = $this->setTotalPages($vars['count'], $vars['limit']);

        $vars['page'] = ($vars['page'] > $totalPages) ? $totalPages : $vars['page'];
        $vars['limit'] = $vars['limit'] < 0 ? 0 : $vars['limit'];
        $vars['offset'] = $vars['limit'] * $vars['page'] - $vars['limit'];
        $vars['offset'] = $vars['offset'] < 0 ? 0 : $vars['offset'];
        $vars['limit'] *= $vars['page'];

        $rows = $this->rapidRecord->getRows($vars);

        if (! is_array($rows) || (isset($rows[0]) && ! is_array($rows[0]))) {
            throw new Exception('The method getRows must return an array of arrays, example: array(array("column1"  =>  "1-1", "column2" => "1-2"), array("column1" => "2-1", "column2" => "2-2"))');
        }

        return [
            'page'    => $vars['page'],
            'total'   => $totalPages,
            'records' => $vars['count'],
            'rows'    => $rows,
        ];
    }

    /**
     * @param $postedData
     * @return int
     */
    public function setPage($postedData)
    {
        return isset($postedData['page']) ? $postedData['page'] : 1;
    }

    /**
     * @param $postedData
     * @return array
     */
    public function setLimit($postedData)
    {
        return isset($postedData['rows']) ? $postedData['rows'] : null;
    }

    /**
     * @param $postedData
     * @return array
     */
    public function setSidx($postedData)
    {
        $sidx = isset($postedData['sidx']) ? $postedData['sidx'] : null;

        return (! $sidx || empty($sidx)) ? null : $sidx;
    }

    /**
     * @param $postedData
     * @return array
     */
    public function setSord($postedData)
    {
        $sord = isset($postedData['sord']) ? $postedData['sord'] : null;

        return (! $sord || empty($sord)) ? null : $sord;
    }

    /**
     * @param $postedData
     * @return array
     */
    public function setFilters($postedData)
    {
        return (isset($postedData['filters']) && ! empty($postedData['filters'])) ? json_decode(str_replace('\'', '"', $postedData['filters']), true) : [];
    }

    /**
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