<?php

namespace App\Jobs;

use App\Exceptions\GoogleFusionTableException;
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
     * @var array
     */
    public $projectIds;

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
     * @param array $projectIds
     */
    public function __construct(array $projectIds = [])
    {
        $this->projectIds = $projectIds;
    }

    /**
     * Execute the job
     *
     * @param ProjectContract $projectContract
     * @param TranscriptionLocationContract $transcriptionLocationContract
     * @param FusionTableService $fusionTableService
     * @throws GoogleFusionTableException
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

        $projects = $this->getProjects();

        $projects->each(function ($project)
        {
            $project->fusion_table_id === null ?
                $this->createProjectFusionTable($project) :
                $this->updateProjectFusionTable($project);
        });
    }

    /**
     * Create fusion table.
     *
     * @param $project
     * @throws GoogleFusionTableException
     */
    public function createProjectFusionTable($project)
    {
        try
        {
            $locations = $this->getProjectLocations($project->id);
            $counts = $this->getProjectLocationsCount($locations);

            $tableId = $this->fusionTableService->createTable($project->title);
            $this->updateProjectTable($project->id, ['fusion_table_id' => $tableId]);

            $this->fusionTableService->createPermission($tableId);

            $this->checkStyleId($project, $tableId, $counts);
            $this->checkTemplateId($project, $tableId);

            $this->fusionTableService->importTableData($tableId, $locations);

        }
        catch (\Exception $e)
        {
            throw new GoogleFusionTableException($e);
        }
    }

    /**
     * Update fusion table.
     *
     * @param $project
     * @throws GoogleFusionTableException
     */
    public function updateProjectFusionTable($project)
    {
        try
        {
            $locations = $this->getProjectLocations($project->id);
            $counts = $this->getProjectLocationsCount($locations);

            $styleId = $this->checkStyleId($project, $project->fusion_table_id, $counts);
            $this->checkTemplateId($project, $project->fusion_table_id);

            $setting = $this->fusionTableService->createTableStyle($project->fusion_table_id, $counts);
            $this->fusionTableService->updateTableStyle($project->fusion_table_id, $styleId, $setting);

            $this->fusionTableService->deleteTableData($project->fusion_table_id);
            $this->fusionTableService->importTableData($project->fusion_table_id, $locations);
        }
        catch (\Exception $e)
        {
            throw new GoogleFusionTableException($e);
        }
    }

    /**
     * Get Projects.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProjects()
    {
        $columns = ['id', 'title', 'fusion_table_id', 'fusion_style_id', 'fusion_template_id'];

        $projects = empty($this->ids) ?
            $this->projectContract->setCacheLifetime(0)
                ->has('transcriptionLocations')
                ->findAll($columns) :
            $this->projectContract->setCacheLifetime(0)
                ->has('transcriptionLocations')
                ->findWhereIn(['id', $this->ids], $columns);

        return $projects;
    }


    /**
     * Get project locations.
     *
     * @param $projectId
     * @return \Illuminate\Support\Collection
     */
    public function getProjectLocations($projectId)
    {
        return $this->transcriptionLocationContract->setCacheLifetime(0)
            ->getTranscriptionFusionTableData($projectId);
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
     * @param $project
     * @param $tableId
     * @param $counts
     * @return integer
     */
    public function checkStyleId($project, $tableId, $counts)
    {
        $styleId = $project->fusion_style_id;

        if ($styleId === 0)
        {
            $settings = $this->fusionTableService->createTableStyle($tableId, $counts);
            $styleId = $this->fusionTableService->insertTableStyle($tableId, $settings);
            $this->updateProjectTable($project->id, ['fusion_style_id' => $styleId]);
        }

        return $styleId;
    }

    /**
     * Check template id.
     *
     * @param $project
     * @param $tableId
     */
    public function checkTemplateId($project, $tableId)
    {
        if ($project->fusion_template_id === 0)
        {
            $templateId = $this->fusionTableService->createTemplate($tableId);
            $this->updateProjectTable($project->id, ['fusion_template_id' => $templateId]);
        }
    }

    /**
     * @param int $id
     * @param array $attributes
     */
    public function updateProjectTable($id, array $attributes = [])
    {
        $this->projectContract->update($id, $attributes);
    }
}
