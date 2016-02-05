<?php namespace Biospex\Repositories\Contracts;

interface Project extends Repository
{
    public function bySlug($slug);

    public function findByUuid($uuid);

    public function getSubjectsAssignedCount($project);
}
