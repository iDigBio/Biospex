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
     * @param bool $trashed
     * @return mixed
     */
    public function getProjectByIdWith($projectId, array $with = [], $trashed = false);

    /**
     * Get random projects for carousel.
     *
     * @param int $count
     * @param array $attributes
     * @return mixed
     */
    public function getRandomProjectsForCarousel($count = 5, array $attributes = ['*']);

    /**
     * Get recent projects for The Projects widget.
     *
     * @param int $count
     * @param array $attributes
     * @return mixed
     */
    public function getRecentProjects($count = 5, array $attributes = ['*']);

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

}