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

use App\Services\Project\HeaderService;

/**
 * Class GridModel
 *
 * Handles the creation and configuration of jqGrid models for data display.
 * This class manages grid column definitions, properties, and formatting
 * for both main grids and subgrids.
 */
class GridModel
{
    /**
     * Default visible columns for the main grid
     */
    private mixed $defaultGridVisible;

    /**
     * Default visible columns for the sub grid
     */
    private mixed $defaultSubGridVisible;

    /**
     * GridModel constructor.
     */
    public function __construct(protected HeaderService $headerService)
    {
        $this->defaultGridVisible = config('config.defaultGridVisible');
        $this->defaultSubGridVisible = config('config.defaultSubGridVisible');
    }

    /**
     * Create the grid model.
     */
    /**
     * Create the grid model with column definitions and configurations.
     *
     * @param  int  $projectId  Project identifier
     * @param  string|null  $route  Current route name
     * @return false|string JSON encoded grid model configuration or false on failure
     */
    public function createGridModel(int $projectId, ?string $route = null): false|string
    {
        $result = $this->headerService->getFirst('project_id', $projectId);

        if (empty($result)) {
            $headers['image'] = [
                'assigned',
                'expedition_ids',
                'exported',
                'imageId',
                'accessURI',
                'ocr',
            ];
        } else {
            $headers = $result->header;
            array_unshift($headers['image'], 'assigned', 'exported', 'expedition_ids', 'imageId');
            $headers['image'][] = 'ocr';
        }

        $colNames = $headers['image'];
        $colModel = $this->setColModel($colNames, $route);

        $subColNames = isset($headers['occurrence']) ? $this->setColNames($headers['occurrence']) : [];
        $subColModel = isset($headers['occurrence']) ? $this->setColModel($subColNames, $route) : [];

        $colNamesResult = array_merge($colNames, $subColNames);
        $colModelResult = array_merge($colModel, $subColModel);

        $data = [
            'colNames' => $colNamesResult,
            'colModel' => $colModelResult,
        ];

        return json_encode($data);
    }

    /**
     * Set column names.
     *
     * @return array
     */
    /**
     * Set column names by prefixing fields with 'occurrence.'
     *
     * @param  array  $fields  List of field names
     * @return array Modified column names
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
     * @return array
     */
    protected function setColModel($colNames, ?string $route = null)
    {
        $colModel = [];
        foreach ($colNames as $column) {
            $colModel[] = $this->formatGridColumn($column, $route);
        }

        return $colModel;
    }

    /**
     * Format a grid column with specific properties based on column type.
     *
     * @param  string  $column  Column name to format
     * @param  string|null  $route  Current route name
     * @return array Column configuration array
     */
    protected function formatGridColumn($column, ?string $route = null): array
    {
        if ($column === 'assigned') {
            return $this->buildAssigned($route);
        }

        if ($column === 'exported') {
            return $this->buildExportedColumn();
        }

        $col = $this->setNormalColumnProperties($column);

        if ($column === 'ocr') {
            $col = array_merge($col, [
                'title' => false,
                'classes' => 'ocrPreview',
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
     */
    protected function buildAssigned(?string $route = null): array
    {
        // Set default value based on route - 'false' (No) for create/clone, 'all' for others
        $defaultValue = ($route === 'create') ? 'false' : 'all';

        return [
            'name' => 'assigned',
            'index' => 'assigned',
            'width' => 35,
            'align' => 'center',
            'hidedlg' => false,
            'stype' => 'select',
            'sortable' => false,
            'searchoptions' => ['defaultValue' => $defaultValue, 'sopt' => ['eq'], 'value' => 'all:All;true:Yes;false:No'],
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
            'name' => 'exported',
            'index' => 'exported',
            'width' => 40,
            'align' => 'center',
            'stype' => 'select',
            'searchoptions' => ['defaultValue' => 'all', 'sopt' => ['eq'], 'value' => 'all:All;true:true;false:false'],
        ];
    }

    /**
     * Set default properties for a normal grid column.
     *
     * @param  string  $column  Column name
     * @param  bool  $image  Whether the column is for image grid
     * @return array Column properties
     */
    protected function setNormalColumnProperties($column, $image = true)
    {
        $default = $image ? $this->defaultGridVisible : $this->defaultSubGridVisible;

        return [
            'name' => $column,
            'index' => $column,
            'key' => false,
            'resizable' => true,
            'search' => $column === 'accessURI' ? false : true,
            'sortable' => true,
            'editable' => false,
            'hidden' => in_array($column, $default) ? false : true,
            'searchoptions' => $this->searchOps($column),
        ];
    }

    /**
     * Define search operations available for a column.
     *
     * @param  string  $column  Column name
     * @return array Search options configuration
     */
    protected function searchOps($column)
    {
        if ($column === 'expedition_ids') {
            return ['sopt' => ['eq', 'ne']];
        }

        return ['sopt' => ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn']];
    }

    /**
     * Add uri link.
     *
     * @return array
     */
    protected function addUriLink($col)
    {
        return array_merge($col, [
            'classes' => 'thumbPreview',
            'formatter' => 'imagePreview',
        ]);
    }
}
