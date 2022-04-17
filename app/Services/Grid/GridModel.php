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

use App\Repositories\HeaderRepository;

/**
 * Class GridModel
 *
 * @package App\Services\Grid
 */
class GridModel
{
    /**
     * @var \App\Repositories\HeaderRepository
     */
    private $headerRepo;

    /**
     * @var
     */
    private $defaultGridVisible;

    /**
     * @var
     */
    private $defaultSubGridVisible;

    /**
     * GridModel constructor.
     *
     * @param \App\Repositories\HeaderRepository $headerRepo
     */
    public function __construct(HeaderRepository $headerRepo)
    {
        $this->headerRepo = $headerRepo;
        $this->defaultGridVisible = config('config.defaultGridVisible');
        $this->defaultSubGridVisible = config('config.defaultSubGridVisible');
    }

    /**
     * Create the grid model.
     *
     * @param int $projectId
     * @return false|string
     */
    public function createGridModel(int $projectId)
    {
        $result = $this->headerRepo->findBy('project_id', $projectId);

        if (empty($result)) {
            $headers['image'] = [
                'assigned',
                'expedition_ids',
                'exported',
                'id',
                'accessURI',
                'ocr'
            ];
        } else {
            $headers = $result->header;
            array_unshift($headers['image'], 'assigned', 'exported', 'expedition_ids', 'id');
            array_push($headers['image'], 'ocr');
        }

        $colNames = $headers['image'];
        $colModel = $this->setColModel($colNames);

        $subColNames = isset($headers['occurrence']) ? $this->setColNames($headers['occurrence']) : [];
        $subColModel = isset($headers['occurrence']) ? $this->setColModel($subColNames) : [];

        $colNamesResult = array_merge($colNames, $subColNames);
        $colModelResult = array_merge($colModel, $subColModel);

        $data = [
            'colNames' => $colNamesResult,
            'colModel' => $colModelResult
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
        if ($column === 'assigned')
        {
            return $this->buildAssigned();
        }

        if ($column === 'exported') {
            return $this->buildExportedColumn();
        }

        $col = $this->setNormalColumnProperties($column);

        if ($column === 'ocr') {
            $col = array_merge($col, [
                'title'    => false,
                'classes'  => 'ocrPreview',
                'cellattr' => 'addDataAttr',
            ]);
        }

        if ($column === 'accessURI') {
            $col = $this->addUriLink($col);
        }

        return $col;
    }

    /**
     * Build expedition checkbox.
     * @return array
     */
    protected function buildAssigned()
    {
        return [
            'name'          => 'assigned',
            'index'         => 'assigned',
            'width'         => 35,
            'align'         => 'center',
            'hidedlg'       => false,
            'stype'         => 'select',
            'sortable'      => false,
            'searchoptions' => ['defaultValue' => 'all', 'sopt' => ['eq'], 'value' => 'all:All;true:Yes;false:No']
        ];
    }

    /**
     * Build expedition checkbox.
     *
     * @return array
     */
    protected function buildExportedColumn()
    {
        return [
            'name'          => 'exported',
            'index'         => 'exported',
            'width'         => 40,
            'align'         => 'center',
            'stype'         => 'select',
            'searchoptions' => ['defaultValue' => 'all', 'sopt' => ['eq'], 'value' => 'all:All;true:true;false:false'],
        ];
    }

    protected function setNormalColumnProperties($column, $image = true)
    {
        $default = $image ? $this->defaultGridVisible : $this->defaultSubGridVisible;

        return [
            'name'          => $column,
            'index'         => $column,
            'key'           => false,
            'resizable'     => true,
            'search'        => $column === 'accessURI' ? false : true,
            'sortable'      => true,
            'editable'      => false,
            'hidden'        => in_array($column, $default) ? false : true,
            'searchoptions' => $this->searchOps($column)
        ];
    }

    protected function searchOps($column)
    {
        if ($column === 'expedition_ids') {
            return ['sopt' => ['eq','ne']];
        }

        return ['sopt' => ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn']];
    }

    /**
     * Add uri link.
     *
     * @param $col
     * @return array
     */
    protected function addUriLink($col)
    {
        return array_merge($col, [
            'classes'   => 'thumbPreview',
            'formatter' => 'imagePreview',
        ]);
    }
}