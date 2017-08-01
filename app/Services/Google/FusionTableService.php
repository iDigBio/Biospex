<?php

namespace App\Services\Google;

class FusionTableService
{

    /**
     * @var FusionTable
     */
    private $fusionTable;

    /**
     * @var FusionTableBucket
     */
    private $fusionTableBucket;

    /**
     * @var GoogleDrive
     */
    private $googleDrive;

    /**
     * FusionTableService constructor.
     * @param FusionTable $fusionTable
     * @param FusionTableBucket $fusionTableBucket
     * @param GoogleDrive $googleDrive
     */
    public function __construct(
        FusionTable $fusionTable,
        FusionTableBucket $fusionTableBucket,
        GoogleDrive $googleDrive
    )
    {
        $this->fusionTable = $fusionTable;
        $this->fusionTableBucket = $fusionTableBucket;
        $this->googleDrive = $googleDrive;

        $this->email = config('mail.from');
        $this->prefix = config('config.nfn_table_prefix');
    }

    /**
     * @param $title
     * @return mixed
     */
    public function createTable($title)
    {
        $title = empty($this->prefix) ? $title : $this->prefix . ' ' . $title;

        $columns = [
            ['setName' => 'State-County', 'setType' => 'STRING'],
            ['setName' => 'Count', 'setType' => 'NUMBER'],
            ['setName' => 'Geometry', 'setType' => 'LOCATION']
        ];
        $tableColumns = $this->fusionTable->createColumns($columns);

        $tableProperties = [
            'setName'         => $title,
            'setColumns'      => $tableColumns,
            'setIsExportable' => true
        ];
        $table = $this->fusionTable->setServiceProperties('Fusiontables_Table', $tableProperties);

        return $this->fusionTable->insertTable($table);
    }

    /**
     * @param $tableId
     */
    public function createPermission($tableId)
    {
        $anyone = [
            'setType' => 'anyone',
            'setRole' => 'reader'
        ];
        $this->googleDrive->createTablePermissions($tableId, $anyone);

        $user = [
            'setType'         => 'user',
            'setRole'         => 'writer',
            'setEmailAddress' => $this->email['address']
        ];
        $this->googleDrive->createTablePermissions($tableId, $user);
    }

    public function createTableStyle($tableId, $counts)
    {
        $buckets = $this->getStyleBuckets($counts);
        $style = [
            'setKind'       => 'fusiontables#buckets',
            'setColumnName' => 'Count',
            'setBuckets'    => $buckets
        ];
        $styleFunction = $this->fusionTable->setServiceProperties('Fusiontables_StyleFunction', $style);

        $polygon = [
            'fillOpacity'        => 1.0,
            'strokeOpacity'      => 1.0,
            'strokeWeight'       => 1,
            'setFillColorStyler' => $styleFunction
        ];
        $polygonStyle = $this->fusionTable->setServiceProperties('Fusiontables_PolygonStyle', $polygon);

        $setting = [
            'setKind'           => 'fusiontables#styleSetting',
            'setTableId'        => $tableId,
            'setPolygonOptions' => $polygonStyle
        ];

        return $this->fusionTable->setServiceProperties('Fusiontables_StyleSetting', $setting);

    }

    /**
     * Update table style.
     *
     * @param $tableId
     * @param $styleId
     * @param $setting
     */
    public function updateTableStyle($tableId, $styleId, $setting)
    {
        return $this->fusionTable->updateTableStyle($tableId, $styleId, $setting);
    }

    /**
     * Get style buckets.
     *
     * @param $counts
     * @return array
     */
    public function getStyleBuckets($counts)
    {
        $bucketCollection = $this->fusionTableBucket->calculateBuckets($counts);

        return array_values($this->fusionTableBucket->fusionTableBuckets($bucketCollection)->toArray());
    }

    /**
     * Insert table style.
     *
     * @param $tableId
     * @param $setting
     * @return mixed
     */
    public function insertTableStyle($tableId, $setting)
    {
        return $this->fusionTable->insertTableStyle($tableId, $setting);
    }

    /**
     * Create table template.
     *
     * @param $tableId
     * @return mixed
     */
    public function createTemplate($tableId)
    {
        $templateProperties = [
            'setKind'                 => 'fusiontables#template',
            'setName'                 => 'Default template',
            'setAutomaticColumnNames' => ['State-County', 'Count']
        ];
        $template = $this->fusionTable->setServiceProperties('Fusiontables_Template', $templateProperties);

        return $this->fusionTable->insertTableTemplate($tableId, $template)->templateId;
    }

    /**
     * Delete table data.
     *
     * @param $tableId
     * @return \Google_Service_Fusiontables_Sqlresponse
     */
    public function deleteTableData($tableId)
    {
        return $this->fusionTable->deleteTableData($tableId);
    }

    /**
     * Import table data.
     *
     * @param $tableId
     * @param $locations
     */
    public function importTableData($tableId, $locations)
    {
        $csv = $this->buildCsvString($locations);

        $reqData = $this->fusionTable->getTable($tableId);

        \Log::alert('Received table reData for:' . $tableId);

        $params = [
            'postBody'   => $reqData,
            'data'       => $csv,
            'mimeType'   => 'application/octet-stream',
            'uploadType' => 'multipart',
            'delimiter'  => ','
        ];

        \Log::alert('Importing Rows for:' . $tableId);
        $this->fusionTable->importRows($tableId, $params);
    }

    /**
     * @param array $locations
     * @return string
     */
    public function buildCsvString($locations)
    {
        # Generate CSV data from array
        $fh = fopen('php://temp', 'rw'); # don't create a file, attempt
        # to use memory instead

        # write out the data
        foreach ($locations as $location)
        {
            if (empty($location->state_county) || null === $location->stateCounty)
            {
                continue;
            }

            $values = [
                $location->state_county,
                $location->count,
                $location->stateCounty->geometry
            ];

            fputcsv($fh, $values);
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

}