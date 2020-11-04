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

use App\Services\Model\GroupService;
use App\Services\Model\WorkflowService;
use Illuminate\Support\Facades\Notification;

/**
 * Class ProjectProcess
 *
 * @package App\Services\Model
 */
class ProjectProcess
{
    /**
     * @var \App\Services\Model\WorkflowService
     */
    private $workflowService;

    /**
     * @var \App\Services\Model\GroupService
     */
    private $groupService;

    /**
     * CommonVariables constructor.
     *
     * @param \App\Services\Model\WorkflowService $workflowService
     * @param \App\Services\Model\GroupService $groupService
     */
    public function __construct(
        WorkflowService $workflowService,
        GroupService $groupService
    )
    {

        $this->workflowService = $workflowService;
        $this->groupService = $groupService;
    }

    /**
     * Return workflow select options.
     */
    public function workflowSelectOptions()
    {
        return $this->workflowService->getWorkflowSelect();
    }

    /**
     * Return group.
     *
     * @param $groupId
     * @return mixed
     */
    public function findGroup($groupId)
    {
        return $this->groupService->find($groupId);
    }

    /**
     * Find users groups.
     *
     * @param $userId
     * @return mixed
     */
    public function getUserGroupCount($userId)
    {
        return $this->groupService->getGroupsByUserId($userId);
    }

    /**
     * Return select options for user's groups
     * @param $user
     * @return mixed
     */
    public function userGroupSelectOptions($user)
    {
        $groups = $this->groupService->getUsersGroupsSelect($user);

        return ['' => '--Select--'] + $groups;
    }

    /**
     * Project status select options.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function statusSelectOptions()
    {
        return config('config.status_select');
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