<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Project extends RepositoryInterface
{
    /**
     * Get list of projects for public project page.
     *
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getPublicProjectIndex($sort = null, $order = null);

    /**
     * Get project by id with relationships.
     *
     * @param $projectId
     * @param array $with
     * @return mixed
     */
    public function getProjectByIdWith($projectId, array $with = []);

    /**
     * Get the project public page.
     *
     * @param $slug
     * @return mixed
     */
    public function getProjectPageBySlug($slug);

    /**
     * @param array $projectIds
     * @return mixed
     */
    public function getProjectsHavingTranscriptionLocations(array $projectIds = []);

    /**
     * @return mixed
     */
    public function getProjectEventSelect();

    /**
     * @param $projectId
     * @return mixed
     */
    public function getProjectForDelete($projectId);

}