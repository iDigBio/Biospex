<?php

namespace App\Jobs;

use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Services\Google\FusionTableService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class NfnClassificationsFusionTableJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * \App\Models\Project
     */
    public $project;

    /**
     * @var int
     */
    public $projectId;

    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * @var TranscriptionLocationContract
     */
    public $transcriptionLocationContract;

    /**
     * @var FusionTableService
     */
    public $fusionTableService;

    /**
     * NfnClassificationsFusionTableJob constructor.
     * @param int $projectId
     */
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Execute the job
     *
     * @param ProjectContract $projectContract
     * @param TranscriptionLocationContract $transcriptionLocationContract
     * @param FusionTableService $fusionTableService
     */
    public function handle(
        ProjectContract $projectContract,
        TranscriptionLocationContract $transcriptionLocationContract,
        FusionTableService $fusionTableService
    )
    {
        $this->projectContract = $projectContract;
        $this->transcriptionLocationContract = $transcriptionLocationContract;
        $this->fusionTableService = $fusionTableService;

        $this->project = $this->projectContract->find($this->projectId);

        try{
            $this->project->fusion_table_id === null ?
                $this->createProjectFusionTable() :
                $this->updateProjectFusionTable();
        }
        catch (\Exception $e)
        {
            $this->delete();
        }
    }

    /**
     * Create fusion table.
     */
    public function createProjectFusionTable()
    {
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
        $locations = $this->getProjectLocations();
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
        return $this->transcriptionLocationContract->setCacheLifetime(0)
            ->getTranscriptionFusionTableData($this->project->id);
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
        })->values()->all();
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
    public function updateProjectTable(array $attributes = [])
    {
        $this->projectContract->update($this->project->id, $attributes);
    }
}
