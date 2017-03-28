<?php

namespace App\Services\Google;

class Table extends GoogleService
{

    /**
     * @var \Google_Service_Fusiontables
     */
    protected $fusionTables;

    /**
     * GoogleFusionTable constructor.
     */
    public function __construct()
    {
        $this->fusionTables = $this->makeService('fusiontables');
    }

    /**
     * @param $columns
     * @return array
     */
    public function createColumns($columns)
    {
        return collect($columns)->map(function ($column)
        {
            return $this->setServiceProperties('Fusiontables_Column', $column);
        })->toArray();
    }

    /**
     * @param $tableId
     * @return \Google_Service_Fusiontables_Table
     */
    public function getTable($tableId)
    {
        return $this->fusionTables->table->get($tableId);
    }

    /**
     * @param $tableId
     * @return \Google_Service_Fusiontables_Table
     */
    public function checkTableExists($tableId)
    {
        return $this->getTable($tableId);
    }

    /**
     * @param $table
     * @return mixed
     */
    public function insertTable($table)
    {
        return $this->fusionTables->table->insert($table)->tableId;
    }

    /**
     * Delete fusion table.
     *
     * @param $tableId
     * @return \GuzzleHttp\Psr7\Response
     */
    public function deleteTable($tableId)
    {
        return $this->fusionTables->table->delete($tableId);
    }

    /**
     * @param $tableId
     * @return \Google_Service_Fusiontables_Sqlresponse
     */
    public function deleteTableData($tableId)
    {
        return $this->fusionTables->query->sql('DELETE FROM ' . $tableId);
    }

    /**
     * @param $tableId
     * @param $params
     * @return \Google_Service_Fusiontables_Import
     */
    public function importRows($tableId, $params)
    {
        return $this->fusionTables->table->importRows($tableId, $params);
    }

    public function listTableStyle($tableId)
    {
        return $this->fusionTables->style->listStyle($tableId);
    }

    /**
     * @param $tableId
     * @param $setting
     * @return mixed
     */
    public function insertTableStyle($tableId, $setting)
    {
        return $this->fusionTables->style->insert($tableId, $setting)->styleId;
    }

    /**
     * @param $tableId
     * @param $styleId
     * @param $setting
     */
    public function updateTableStyle($tableId, $styleId, $setting)
    {
        $this->fusionTables->style->update($tableId, $styleId, $setting);
    }

    /**
     * @param $tableId
     * @return \Google_Service_Fusiontables_TemplateList
     */
    public function listTableTemplate($tableId)
    {
        return $this->fusionTables->template->listTemplate($tableId);
    }

    /**
     * @param $tableId
     * @param $template
     * @return \Google_Service_Fusiontables_Template
     */
    public function insertTableTemplate($tableId, $template)
    {
        return $this->fusionTables->template->insert($tableId, $template);
    }
}