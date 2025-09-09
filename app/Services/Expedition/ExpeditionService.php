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

namespace App\Services\Expedition;

use App\Models\Expedition;
use App\Models\Project;
use App\Models\User;
use App\Services\Subject\SubjectService;
use App\Services\Trait\ExpeditionPartitionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class ExpeditionService
{
    use ExpeditionPartitionTrait;

    private Collection $subjectIds;

    /**
     * Create a new instance of ExpeditionService.
     */
    public function __construct(
        protected Expedition $expedition,
        protected SubjectService $subjectService
    ) {}

    /**
     * Create Expedition and return.
     */
    public function store(Project $project, array $request): mixed
    {
        // Handle logo upload for new expeditions
        $this->handleLogoUploadForCreate($request);

        $request['project_id'] = $project->id;
        $expedition = $this->expedition->create($request);

        $expedition->load(['project', 'workflow.actors.contacts']);

        $this->setSubjectIds($request['subject-ids']);
        $this->attachSubjects($expedition->id);
        $this->syncActors($expedition);
        $this->syncStat($expedition);

        $this->notifyActorContacts($expedition, $project);

        return $expedition;
    }

    /**
     * Get expeditions for admin index.
     */
    public function getAdminIndex(User $user, array $request = []): Collection
    {
        $query = $this->expedition->with([
            'project.group',
            'stat',
            'panoptesProject',
            'workflowManager',
            'zooniverseExport',
        ])->whereHas('project.group.users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });

        $sortedRecords = $this->sortRecords($query, $request);

        return $this->partitionExpeditions($sortedRecords);
    }

    /**
     * Get expeditions for public index.
     */
    public function getPublicIndex(array $request = []): Collection
    {
        $query = $this->expedition->with('project:id,slug')
            ->has('panoptesProject')->whereHas('actors', function ($q) {
                $q->zooniverse();
            })
            ->with('panoptesProject', 'stat', 'zooActorExpedition');

        $sortedResults = $this->sortRecords($query, $request);

        return $this->partitionExpeditions($sortedResults);
    }

    /**
     * Sort results for expedition indexes.
     */
    protected function sortRecords(Builder $query, array $request = []): \Illuminate\Database\Eloquent\Collection
    {
        $records = ! isset($request['projectId']) ?
            $query->get() :
            $query->where('project_id', $request['projectId'])->get();

        if (! isset($request['order'])) {
            return $records;
        }

        match ($request['sort']) {
            'title' => $records = $request['order'] === 'desc' ?
                $records->sortByDesc('title') : $records->sortBy('title'),
            'project' => $records = $request['order'] === 'desc' ?
                $records->sortByDesc(fn ($expedition) => $expedition->project->title) :
                $records->sortBy(fn ($expedition) => $expedition->project->title),
            'date' => $records = $request['order'] === 'desc' ?
                $records->sortByDesc('created_at') :
                $records->sortBy('created_at'),
        };

        return $records;
    }

    /**
     * Get subject id count.
     */
    public function getSubjectCount(): int
    {
        return $this->subjectIds->count();
    }

    /**
     * Set subject ids.
     */
    public function setSubjectIds(?string $subjectIds = null): void
    {
        $this->subjectIds = $subjectIds === null ? collect([]) : collect(explode(',', $subjectIds));
    }

    /**
     * Get subject ids assigned to expedition.
     */
    public function getSubjectIdsByExpeditionId(Expedition $expedition): Collection
    {
        return $this->subjectService->subject->where('expedition_ids', $expedition->id)->get(['_id'])->pluck('_id');
    }

    /**
     * Update subjects for expedition if changed and if workflow manager does not exist.
     */
    public function updateSubjects(Expedition $expedition): void
    {
        if ($expedition->workflowManager !== null) {
            return;
        }

        $oldIds = $this->getSubjectIdsByExpeditionId($expedition);
        $newIds = $this->subjectIds;

        $detachIds = $oldIds->diff($newIds);
        $attachIds = $newIds->diff($oldIds);

        $this->detachSubjects($expedition->id, $detachIds);
        $this->attachSubjects($expedition->id, $attachIds);

        $this->syncStat($expedition);
    }

    /**
     * Detach subjects from expedition.
     */
    public function detachSubjects(int $expeditionId, Collection $detachIds): void
    {
        $this->subjectService->detachSubjects($detachIds, $expeditionId);
    }

    /**
     * Attach subjects to expedition.
     */
    public function attachSubjects(int $expeditionId, ?Collection $attachIds = null): void
    {
        $attachIds = $attachIds === null ? $this->subjectIds : $attachIds;

        $this->subjectService->attachSubjects($attachIds, $expeditionId);
    }

    /**
     * Sync the actors depending on workflow chosen.
     */
    public function syncActors(Expedition $expedition): void
    {
        $actors = $expedition->workflow->actors->mapWithKeys(function ($actor) use ($expedition) {
            return $expedition->actors->contains('id', $actor->id) ? [$actor->id => ['order' => $actor->pivot->order]] : [
                $actor->id => [
                    'state' => 0,
                    'order' => $actor->pivot->order,
                    'total' => $this->getSubjectCount(),
                ],
            ];
        })->toArray();

        $expedition->actors()->sync($actors);
    }

    /**
     * Update or create expedition stat.
     */
    public function syncStat(Expedition $expedition): void
    {
        $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], ['local_subject_count' => $this->getSubjectCount()]);
    }

    /**
     * Send notifications for new projects and actors.
     *
     * @see \App\Notifications\ZooniverseNewExpedition
     */
    public function notifyActorContacts($expedition, $project): void
    {
        $newNotification = config('zooniverse.new_expedition_notification');

        $expedition->workflow->actors->reject(function ($actor) {
            return $actor->contacts->isEmpty();
        })->filter(function ($actor) use ($newNotification) {
            return isset($newNotification[$actor->id]);
        })->each(function ($actor) use ($project, $expedition, $newNotification) {
            $class = '\App\Notifications\\'.$newNotification[$actor->id];
            if (class_exists($class)) {
                Notification::send($actor->contacts, new $class($project, $expedition));
            }
        });
    }

    /**
     * Update Expedition.
     * If expedition is completed and unlocked, this is a first change. If workflow id
     */
    public function update(Expedition $expedition, array $request): Expedition
    {
        // Handle logo upload and removal
        $this->handleLogoUpload($request, $expedition);

        $expedition->completed = $this->setExpeditionCompleted($expedition, $request['workflow_id']);

        $expedition->fill($request)->save();

        $expedition->load(['actors', 'workflow.actors', 'workflowManager']);

        $this->setSubjectIds($request['subject-ids']);
        $this->updateSubjects($expedition);
        $this->syncActors($expedition);

        return $expedition;
    }

    /**
     * Reset Expedition completed according to workflow chosen.
     */
    private function setExpeditionCompleted(Expedition $expedition, int $workflow_id): int
    {
        return ($expedition->completed && ! $expedition->locked &&
            $workflow_id == config('geolocate.workflow_id')) ? 0 : $expedition->completed;
    }

    /**
     * Get expeditions for Zooniverse processing.
     */
    public function getExpeditionsForZooniverseProcess(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->expedition->whereHas('panoptesProject')->whereHas('actors', function ($q) {
            $q->zooniverse();
        })->where('completed', 0)->get();
    }

    /**
     * Get expedition download by actor.
     */
    public function getExpeditionDownloadsByActor(Expedition &$expedition): \Illuminate\Database\Eloquent\Model
    {
        return $expedition->load([
            'project.group',
            'actors.downloads' => function ($query) use ($expedition) {
                $query->where('expedition_id', $expedition->id);
            },
        ]);
    }

    /**
     * Find expedition having workflow manager by id.
     */
    public function findExpeditionHavingWorkflowManager($expeditionId): ?Expedition
    {
        return $this->expedition->has('workflowManager')->find($expeditionId);
    }

    /**
     * Get expedition for home page visuals.
     */
    public function getHomePageProjectExpedition(): \Illuminate\Database\Eloquent\Model
    {
        return $this->expedition->with([
            'project' => function ($q) {
                $q->withCount('expeditions')
                    ->withSum('expeditionStats', 'transcriptions_completed')
                    ->withSum('expeditionStats', 'transcriber_count')
                    ->withCount('events');
            },
        ])->with('panoptesProject')->whereHas('stat', function ($q) {
            $q->whereBetween('percent_completed', [0.00, 99.99]);
        })->with([
            'stat' => function ($q) {
                $q->whereBetween('percent_completed', [0.00, 99.99]);
            },
        ])->where('project_id', 13)->inRandomOrder()->first();
    }

    /**
     * Get expedition for Zooniverse process.
     *
     * @see ZooniverseCsvService::getExpedition()
     */
    public function getExpeditionForZooniverseProcess(int $expeditionId): \Illuminate\Database\Eloquent\Model
    {
        return $this->expedition->with(['panoptesProject'])
            ->has('panoptesProject')->whereHas('actors', function ($q) {
                $q->zooniverse();
            })->where('completed', 0)->find($expeditionId);
    }

    /**
     * Get expedition for queue reset.
     */
    public function getExpeditionForQueueReset(int $expeditionId): Expedition
    {
        return $this->expedition->with(['zooActorExpedition.actor', 'stat', 'exportQueue'])->find($expeditionId);
    }

    /**
     * Handle logo upload for new expeditions.
     */
    private function handleLogoUploadForCreate(array &$data): void
    {
        // If logo_path is empty, remove it from data so it doesn't overwrite with null
        if (isset($data['logo_path']) && empty($data['logo_path'])) {
            unset($data['logo_path']);
        }
    }

    /**
     * Handle logo upload and remove old logo if new one is uploaded.
     */
    private function handleLogoUpload(array &$data, Expedition $expedition): void
    {
        // Check if there's a new logo uploaded via Livewire
        if (isset($data['logo_path']) && ! empty($data['logo_path'])) {
            // Remove old logo if it exists
            $this->removeOldLogo($expedition);

            // The new logo_path will be set via $expedition->fill($data)
        }

        // If logo_path is empty but was set before, keep the existing one
        if (isset($data['logo_path']) && empty($data['logo_path']) && ! empty($expedition->logo_path)) {
            unset($data['logo_path']); // Don't overwrite with empty value
        }
    }

    /**
     * Remove old logo files from storage.
     */
    private function removeOldLogo(Expedition $expedition): void
    {
        if (empty($expedition->logo_path)) {
            return;
        }

        try {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

            // Remove the main logo file
            if (\Storage::disk($disk)->exists($expedition->logo_path)) {
                \Storage::disk($disk)->delete($expedition->logo_path);
            }

            // Remove any variants if they exist (medium, small, etc.)
            $logoDirectory = dirname($expedition->logo_path);
            $logoFilename = basename($expedition->logo_path);

            // Check for variant directories (medium, small, etc.)
            $variantDirs = ['medium', 'small'];
            foreach ($variantDirs as $variant) {
                $variantPath = $logoDirectory.'/'.$variant.'/'.$logoFilename;
                if (\Storage::disk($disk)->exists($variantPath)) {
                    \Storage::disk($disk)->delete($variantPath);
                }
            }

        } catch (\Exception $e) {
            // Log error but don't fail the update
            \Log::error("Failed to remove old logo for expedition {$expedition->id}: ".$e->getMessage());
        }
    }
}
