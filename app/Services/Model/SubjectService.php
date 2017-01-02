<?php

namespace App\Services\Model;

use App\Repositories\Contracts\Subject;

class SubjectService
{

    /**
     * @var Subject
     */
    public $repository;

    /**
     * SubjectService constructor.
     * @param Subject $repository
     */
    public function __construct(Subject $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Detach subjects from expeditions.
     *
     * @param $subjects
     * @param $id
     */
    public function detach($subjects, $id)
    {
        $ids = $subjects->pluck('_id');
        $this->repository->detachSubjects($ids, $id);
    }

}