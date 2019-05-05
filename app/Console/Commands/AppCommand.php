<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\TranscriptionLocation;
use App\Services\Google\FusionTableService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Repositories\Interfaces\TranscriptionLocation
     */
    private $transcriptionLocationContract;

    /**
     * @var \App\Services\Google\FusionTableService
     */
    private $fusionTableService;

    private $projectId;
    private $project;

    /**
     * AppCommand constructor.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\TranscriptionLocation $transcriptionLocationContract
     * @param \App\Services\Google\FusionTableService $fusionTableService
     */
    public function __construct(
        Project $projectContract,
        TranscriptionLocation $transcriptionLocationContract,
        FusionTableService $fusionTableService
    )
    {
        parent::__construct();
        $this->projectContract = $projectContract;
        $this->transcriptionLocationContract = $transcriptionLocationContract;
        $this->fusionTableService = $fusionTableService;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->projectId = 17;
        $this->project = $this->projectContract->find($this->projectId);

        try{
            $this->project->fusion_table_id === null ?
                $this->createProjectFusionTable() :
                $this->updateProjectFusionTable();
        }
        catch (Exception $e)
        {
            echo $e->getFile() . PHP_EOL;
            echo $e->getLine() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
        }

    }

    /**
     * Create fusion table.
     */
    public function createProjectFusionTable()
    {
        dd('stop');
        $locations = $this->getProjectLocations();
        $counts = $this->getProjectLocationsCount($locations);

        $tableId = $this->fusionTableService->createTable($this->project->title);
        $this->updateProjectTable(['fusion_table_id' => $tableId]);

        $this->fusionTableService->createPermission($tableId);

        $this->checkStyleId($tableId, $counts);
        $this->checkTemplateId($tableId);

        $this->fusionTableService->importTableData($tableId, $locations);
    }

    /**
     * Update fusion table.
     */
    public function updateProjectFusionTable()
    {
        $locations = $this->getProjectLocations()->filter(function($location){
            return isset($location->stateCounty);
        });
        $counts = $this->getProjectLocationsCount($locations);

        $styleId = $this->checkStyleId($this->project->fusion_table_id, $counts);
        $this->checkTemplateId($this->project->fusion_table_id);

        $setting = $this->fusionTableService->createTableStyle($this->project->fusion_table_id, $counts);
        $this->fusionTableService->updateTableStyle($this->project->fusion_table_id, $styleId, $setting);
        $this->fusionTableService->deleteTableData($this->project->fusion_table_id);
        $this->fusionTableService->importTableData($this->project->fusion_table_id, $locations);
    }

    /**
     * Get project locations.
     *
     * @return Collection
     */
    public function getProjectLocations()
    {
        return $this->transcriptionLocationContract->getTranscriptionFusionTableData($this->project->id);
    }

    /**
     * Get counts of locations.
     *
     * @param Collection $locations
     * @return mixed
     */
    public function getProjectLocationsCount($locations)
    {
        return $locations->pluck('count')->sort()->filter(function ($location)
        {
            return $location > 0;
        })->values();
    }

    /**
     * Check style id.
     *
     * @param $tableId
     * @param $counts
     * @return integer
     */
    public function checkStyleId($tableId, $counts)
    {
        $styleId = $this->project->fusion_style_id;

        if ($styleId === 0)
        {
            $settings = $this->fusionTableService->createTableStyle($tableId, $counts);
            $styleId = $this->fusionTableService->insertTableStyle($tableId, $settings);
            $this->updateProjectTable(['fusion_style_id' => $styleId]);
        }

        return $styleId;
    }

    /**
     * Check template id.
     *
     * @param $tableId
     */
    public function checkTemplateId($tableId)
    {
        if ($this->project->fusion_template_id === 0)
        {
            $templateId = $this->fusionTableService->createTemplate($tableId);
            $this->updateProjectTable(['fusion_template_id' => $templateId]);
        }
    }

    /**
     * @param array $attributes
     */
    public function updateProjectTable(array $attributes)
    {
        $this->projectContract->update($attributes, $this->project->id);
    }
}
