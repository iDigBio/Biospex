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

namespace App\Services\Models;

use App\Http\Requests\ExpeditionFormRequest;
use App\Models\Expedition;
use App\Repositories\ExpeditionRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\WorkflowRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class ExpeditionService
{
    private Collection $subjectIds;

    private ExpeditionRepository $expeditionRepository;

    private SubjectRepository $subjectRepository;

    private WorkflowRepository $workflowRepository;

    public function __construct(
        ExpeditionRepository $expeditionRepository,
        SubjectRepository $subjectRepository,
        WorkflowRepository $workflowRepository
    ) {
        $this->expeditionRepository = $expeditionRepository;
        $this->subjectRepository = $subjectRepository;
        $this->workflowRepository = $workflowRepository;
    }

    /**
     * Create Expedition and return.
     */
    public function createExpedition(array $request): mixed
    {
        return $this->expeditionRepository->create($request);
    }

    /**
     * Find Expedition with relations.
     * Used for Expedition panel on multiple pages. Keeping it cached prevents overloading.
     */
    public function findExpeditionWithRelations(int $expeditionId, array $relations = []): mixed
    {
        return $this->expeditionRepository->findWith($expeditionId, $relations);
    }

    /**
     * Get subject ids assigned to expedition.
     */
    public function getSubjectIdsByExpeditionId(int $expeditionId): Collection
    {
        return collect($this->subjectRepository->findByExpeditionId((int) $expeditionId, ['_id'])->pluck('_id'));
    }

    /**
     * Update for new GeoLocateExport actor.
     */
    public function updateForGeoLocate(int $expeditionId, ExpeditionFormRequest $request): Expedition
    {
        $expedition = $this->findExpeditionWithRelations($expeditionId);

        $expedition->completed = $this->setExpeditionCompleted($expedition, $request->get('workflow_id'));

        $expedition->fill($request->all())->save();

        $expedition->load(['actors', 'workflow.actors', 'workflowManager']);

        return $expedition;
    }

    /**
     * Reset Expedition completed according to workflow chosen.
     */
    private function setExpeditionCompleted(Expedition $expedition, int $workflow_id): int
    {
        return ($expedition->completed && ! $expedition->locked && $workflow_id == config('geolocate.workflow_id')) ? 0 : $expedition->completed;
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
     * Update subjects for expedition if changed and if workflow manager does not exist.
     */
    public function updateSubjects(Expedition $expedition): void
    {
        if ($expedition->workflowManager !== null) {
            return;
        }

        $oldIds = $this->getSubjectIdsByExpeditionId($expedition->id);
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
        $this->subjectRepository->detachSubjects($detachIds, $expeditionId);
    }

    /**
     * Attach subjects to expedition.
     */
    public function attachSubjects(int $expeditionId, ?Collection $attachIds = null): void
    {
        $attachIds = $attachIds === null ? $this->subjectIds : $attachIds;

        $this->subjectRepository->attachSubjects($attachIds, $expeditionId);
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
     * Return workflow select options.
     *
     * @return array|string[]
     */
    public function getWorkflowSelect(): array
    {
        return ['' => '--Select--'] + $this->workflowRepository->getWorkflowSelect();
    }

    /**
     * Get expeditions for admin index page.
     */
    public function getAdminIndex(?int $userId = null, $sort = null, $order = null, $projectId = null): mixed
    {
        return $this->expeditionRepository->getExpeditionAdminIndex($userId, $sort, $order, $projectId);
    }
}
