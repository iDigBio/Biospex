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

namespace App\Services\Process;

use App\Repositories\GroupRepository;
use App\Repositories\WorkflowRepository;
use Illuminate\Support\Facades\Notification;

/**
 * Class ProjectProcess
 *
 * @package App\Repositories
 */
class ProjectProcess
{
    /**
     * @var \App\Repositories\WorkflowRepository
     */
    private $workflowRepo;

    /**
     * @var \App\Repositories\GroupRepository
     */
    private $groupRepo;

    /**
     * CommonVariables constructor.
     *
     * @param \App\Repositories\WorkflowRepository $workflowRepo
     * @param \App\Repositories\GroupRepository $groupRepo
     */
    public function __construct(
        WorkflowRepository $workflowRepo,
        GroupRepository $groupRepo
    )
    {

        $this->workflowRepo = $workflowRepo;
        $this->groupRepo = $groupRepo;
    }

    /**
     * Return workflow select options.
     */
    public function workflowSelectOptions()
    {
        return $this->workflowRepo->getWorkflowSelect();
    }

    /**
     * Return group.
     *
     * @param $groupId
     * @return mixed
     */
    public function findGroup($groupId)
    {
        return $this->groupRepo->find($groupId);
    }

    /**
     * Find users groups.
     *
     * @param $userId
     * @return int
     */
    public function getUserGroupCount($userId): int
    {
        return $this->groupRepo->getUserGroupCount($userId);
    }

    /**
     * Return select options for user's groups.
     *
     * @param $user
     * @return mixed
     */
    public function userGroupSelectOptions($user)
    {
        $groups = $this->groupRepo->getUsersGroupsSelect($user);

        return ['' => '--Select--'] + $groups;
    }

    /**
     * Send notifications for new projects and actors.
     *
     * @param $project
     *
     */
    public function notifyActorContacts($project)
    {
        $nfnNotify = config('config.nfnNotify');

        $project->workflow->actors->reject(function ($actor) {
            return $actor->contacts->isEmpty();
        })->filter(function ($actor) use ($nfnNotify) {
            return isset($nfnNotify[$actor->id]);
        })->each(function ($actor) use ($project, $nfnNotify) {
            $class = '\App\Notifications\\'.$nfnNotify[$actor->id];
            if (class_exists($class)) {
                Notification::send($actor->contacts, new $class($project));
            }
        });
    }
}