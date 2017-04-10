<?php

namespace App\Jobs;

use App\Exceptions\GoogleFusionTableException;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Services\Google\Bucket;
use App\Services\Google\Table;
use App\Services\Google\Drive;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Config;

class NfnClassificationsFusionTableJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * @var array
     */
    public $projectIds;

    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * @var Table
     */
    public $table;

    /**
     * @var Drive
     */
    public $drive;

    /**
     * @var Bucket
     */
    public $bucket;

    /**
     * @var mixed
     */
    public $email;

    /**
     * @var mixed
     */
    public $prefix;

    /**
     * Create a new job instance.
     *
     * @param array $projectIds
     */
    public function __construct(array $projectIds = [])
    {
        $this->projectIds = $projectIds;
        $this->email = Config::get('mail.from');
        $this->prefix = Config::get('config.nfn_table_prefix');
    }

    /**
     * Execute the job.
     *
     * @param ProjectContract $projectContract
     * @param TranscriptionLocationContract $locationContract
     * @param Table $table
     * @param Bucket $bucket
     * @param Drive $drive
     * @return void
     * @throws GoogleFusionTableException
     */
    public function handle(
        ProjectContract $projectContract,
        TranscriptionLocationContract $locationContract,
        Table $table,
        Bucket $bucket,
        Drive $drive
    )
    {
        $this->projectContract = $projectContract;
        $this->drive = $drive;
        $this->table = $table;
        $this->bucket = $bucket;

        $hasRelations = ['transcriptionLocations'];
        $columns = ['id', 'title', 'fusion_table_id', 'fusion_style_id'];

        $projects = empty($this->ids) ?
            $projectContract->setCacheLifetime(0)
                ->findAllHasRelationsWithRelations($hasRelations, [], $columns) :
            $projectContract->setCacheLifetime(0)
                ->findWhereInHasRelationsWithRelations(['id', $this->ids], $hasRelations, [], $columns);

        $projects->each(function ($project) use ($locationContract)
        {
            $locations = $this->getProjectLocations($locationContract, $project->id);
            $counts = $this->getProjectLocationsCount($locations);
            $project->fusion_table_id === null ?
                $this->createProjectFusionTable($project, $locations, $counts) :
                $this->updateProjectFusionTable($project, $locations, $counts);
        });
    }

    public function getProjectLocations(TranscriptionLocationContract $locationContract, $projectId)
    {
        return $locationContract->setCacheLifetime(0)
            ->getTranscriptionFusionTableData($projectId);
    }

    public function getProjectLocationsCount($locations)
    {
        return $locations->pluck('count')->sort()->filter(function ($location)
        {
            return $location > 0;
        })->values()->all();
    }

    public function createProjectFusionTable($project, $locations, $counts)
    {
        try
        {
            $title = empty($this->prefix) ? $project->title : $this->prefix . ' ' . $project->title;
            $tableId = $this->createTable($title);
            $this->createPermission($tableId);
            $settings = $this->createTableStyle($tableId, $counts);
            $styleId = $this->table->insertTableStyle($tableId, $settings);
            $templateId = $this->createTemplate($tableId);
            $this->importTableData($tableId, $locations);

            $attributes = [
                'fusion_table_id'    => $tableId,
                'fusion_style_id'    => $styleId,
                'fusion_template_id' => $templateId
            ];
            $this->projectContract->update($project->id, $attributes);
        }
        catch (\Exception $e)
        {
            throw new GoogleFusionTableException($e);
        }
    }

    public function updateProjectFusionTable($project, $locations, $counts)
    {
        try
        {
            $this->table->deleteTableData($project->fusion_table_id);
            $this->importTableData($project->fusion_table_id, $locations);

            $setting = $this->createTableStyle($project->fusion_table_id, $counts);
            $this->table->updateTableStyle($project->fusion_table_id, $project->fusion_style_id, $setting);
        }
        catch (\Exception $e)
        {
            throw new GoogleFusionTableException($e);
        }
    }

    public function createTable($title)
    {
        $columns = [
            ['setName' => 'State-County', 'setType' => 'STRING'],
            ['setName' => 'Count', 'setType' => 'NUMBER'],
            ['setName' => 'Geometry', 'setType' => 'LOCATION']
        ];
        $tableColumns = $this->table->createColumns($columns);

        $tableProperties = [
            'setName'         => $title,
            'setColumns'      => $tableColumns,
            'setIsExportable' => true
        ];
        $table = $this->table->setServiceProperties('Fusiontables_Table', $tableProperties);

        return $this->table->insertTable($table);
    }

    public function createPermission($tableId)
    {
        $anyone = [
            'setType' => 'anyone',
            'setRole' => 'reader'
        ];
        $this->drive->createTablePermissions($tableId, $anyone);

        $user = [
            'setType'         => 'user',
            'setRole'         => 'writer',
            'setEmailAddress' => $this->email['address']
        ];
        $this->drive->createTablePermissions($tableId, $user);
    }

    public function createTemplate($tableId)
    {
        $templateProperties = [
            'setKind'                 => 'fusiontables#template',
            'setName'                 => 'Default template',
            'setAutomaticColumnNames' => ['State-County', 'Count']
        ];
        $template = $this->table->setServiceProperties('Fusiontables_Template', $templateProperties);

        return $this->table->insertTableTemplate($tableId, $template)->templateId;
    }

    public function getStyleBuckets($counts)
    {
        $bucketCollection = $this->bucket->calculateBuckets($counts);

        return array_values($this->bucket->fusionTableBuckets($bucketCollection)->toArray());
    }

    public function createTableStyle($tableId, $counts)
    {
        $buckets = $this->getStyleBuckets($counts);
        $style = [
            'setKind'       => 'fusiontables#buckets',
            'setColumnName' => 'Count',
            'setBuckets'    => $buckets
        ];
        $styleFunction = $this->table->setServiceProperties('Fusiontables_StyleFunction', $style);

        $polygon = [
            'fillOpacity'        => 1.0,
            'strokeOpacity'      => 1.0,
            'strokeWeight'       => 1,
            'setFillColorStyler' => $styleFunction
        ];
        $polygonStyle = $this->table->setServiceProperties('Fusiontables_PolygonStyle', $polygon);

        $setting = [
            'setKind'           => 'fusiontables#styleSetting',
            'setTableId'        => $tableId,
            'setPolygonOptions' => $polygonStyle
        ];

        return $this->table->setServiceProperties('Fusiontables_StyleSetting', $setting);

    }

    public function importTableData($tableId, $locations)
    {
        $csv = $this->buildCsvString($locations);

        $reqData = $this->table->getTable($tableId);

        $params = [
            'postBody'   => $reqData,
            'data'       => $csv,
            'mimeType'   => 'application/octet-stream',
            'uploadType' => 'multipart',
            'delimiter'  => ','
        ];

        $this->table->importRows($tableId, $params);
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
