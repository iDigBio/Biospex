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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Project;

use App\Facades\CountHelper;
use App\Models\Project;
use App\Models\ProjectResource;
use App\Models\User;
use App\Services\ExpeditionPartitionTrait;
use App\Services\Helpers\CountService;
use Illuminate\Support\Collection;

readonly class ProjectService
{
    use ExpeditionPartitionTrait;

    /**
     * ProjectService constructor.
     */
    public function __construct(
        public Project $project,
        public ProjectResource $projectResource,
        public CountService $countService,
    ) {}

    /**
     * Get all with relations.
     */
    public function findWithRelations(int $id, array $relations = []): mixed
    {
        return $this->project->with($relations)->find($id);
    }

    /**
     * Get select for project.
     *
     * @return array|string[]
     */
    public function getProjectEventSelect(): array
    {
        $results = $this->project->has('panoptesProjects')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->pluck('title', 'id');

        return ['' => 'Select'] + $results->toArray();
    }

    /**
     * Override create in base repository.
     *
     * @return \App\Models\Project|\Illuminate\Database\Eloquent\Model|true
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model|bool|Project
    {
        $project = $this->project->create($data);

        if (! isset($data['resources'])) {
            return true;
        }

        $resources = collect($data['resources'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->map(function ($resource) {
            return $this->projectResource::make($resource);
        });

        $project->resources()->saveMany($resources->all());

        return $project;
    }

    /**
     * Update project.
     */
    public function update(array $data, Project $project): array|bool
    {
        $project->fill($data)->save();

        if (! isset($data['resources'])) {
            return true;
        }

        $resources = collect($data['resources'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->reject(function ($resource) {
            return ! empty($resource['id']) && $this->updateProjectResource($resource);
        })->map(function ($resource) {
            return $this->projectResource::make($resource);
        });

        if ($resources->isEmpty()) {
            return true;
        }

        return $project->resources()->saveMany($resources->all());
    }

    /**
     * Get projects for admin index page.
     */
    public function getAdminIndex(User $user, array $request = []): Collection
    {
        $records = $this->project->withCount('expeditions')->with([
            'group' => function ($q) use ($user) {
                $q->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            },
        ])->whereHas('group', function ($q) use ($user) {
            $q->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->get();

        return $this->sortResults($records, $request);
    }

    /**
     * Get public project index page.
     */
    public function getPublicProjectIndex(array $request = []): Collection
    {
        $results = $this->project->withCount('expeditions')
            ->withCount('events')->with('group')->has('panoptesProjects')->get();

        return $this->sortResults($results, $request);
    }

    /**
     * Get project for show page.
     */
    public function getProjectShow(Project &$project): array
    {
        $project->loadCount('expeditions')->load([
            'group',
            'ocrQueue',
            'expeditions' => function ($q) {
                $q->with(['stat', 'zooniverseExport', 'panoptesProject', 'workflowManager']);
            },
        ]);

        [$expeditions, $expeditionsCompleted] = $this->partitionRecords($project->expeditions);

        $transcriptionsCount = CountHelper::projectTranscriptionCount($project->id);
        $transcribersCount = CountHelper::projectTranscriberCount($project->id);

        return [
            'project' => $project,
            'group' => $project->group,
            'expeditions' => $expeditions,
            'expeditionsCompleted' => $expeditionsCompleted,
            'transcriptionsCount' => $transcriptionsCount,
            'transcribersCount' => $transcribersCount,
        ];
    }

    /**
     * Get project page by slug.
     */
    public function getProjectPageBySlug($slug): ?Project
    {
        return $this->project->withCount('events')->withCount('expeditions')->with([
            'amChart',
            'group.users.profile',
            'resources',
            'lastPanoptesProject',
            'bingos',
            'expeditions' => function ($query) {
                $query->has('panoptesProject')->has('zooniverseActor')->with('panoptesProject', 'stat', 'zooniverseActor');
            },
            'events' => function ($q) {
                $q->with('teams');
                $q->orderBy('start_date', 'desc');
            }])->where('slug', '=', $slug)->first();
    }

    /**
     * Get project for deletion.
     */
    public function loadRelationsForDelete(Project &$project): void
    {
        $project->load([
            'group',
            'panoptesProjects',
            'workflowManagers',
            'expeditions.downloads',
        ]);
    }

    /**
     * Filter or delete resource.
     */
    public function filterOrDeleteResources($resource): bool
    {
        if ($resource['type'] === null) {
            return true;
        }

        if (strtolower($resource['type']) === 'delete') {
            ProjectResource::destroy($resource['id']);

            return true;
        }

        return false;
    }

    /**
     * Update project resource.
     */
    public function updateProjectResource($resource): bool
    {
        $record = ProjectResource::find($resource['id']);
        $record->type = $resource['type'];
        $record->name = $resource['name'];
        $record->description = $resource['description'];
        if (isset($resource['download'])) {
            $record->download = $resource['download'];
        }

        $record->save();

        return true;
    }

    /**
     * Sort results from index pages.
     */
    protected function sortResults(Collection $records, array $request = []): Collection
    {
        if (! isset($request['order'])) {
            return $records->sortBy('created_at');
        }

        // use match instead of switch
        match ($request['sort']) {
            'title' => $results = $request['order'] === 'desc' ?
                $records->sortByDesc('title') :
                $records->sortBy('title'),
            'group' => $results = $request['order'] === 'desc' ?
                $records->sortByDesc(fn ($project) => $project->group->title) :
                $records->sortBy(fn ($project) => $project->group->title),
            'date' => $results = $request['order'] === 'desc' ?
                $records->sortByDesc('created_at') :
                $records->sortBy('created_at'),
        };

        return $results;
    }

    /**
     * Get project for amChart.
     */
    public function getProjectForAmChartJob($projectId): ?Project
    {
        return $this->project->with([
            'amChart',
            'expeditions' => function ($q) {
                $q->with('stat')->has('stat');
                $q->with('panoptesProject')->has('panoptesProject');
            },
        ])->find($projectId);
    }

    /**
     * Get project for Darwin import job.
     */
    public function getProjectForDarwinImportJob($projectId): ?Project
    {
        return $this->project->with(['group' => function ($q) {
            $q->with(['owner', 'users' => function ($q) {
                $q->where('notification', 1);
            }]);
        }])->find($projectId);
    }
}
