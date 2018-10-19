<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Project extends RepositoryInterface
{

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

    /**
     * @param array $attributes
     * @return mixed
     */
    public function getProjectForHomePage(array $attributes = []);

}