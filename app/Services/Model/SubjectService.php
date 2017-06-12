<?php

namespace App\Services\Model;

use App\Repositories\Contracts\SubjectContract;

class SubjectService
{

    /**
     * @var SubjectContract
     */
    public $subjectContract;

    /**
     * SubjectService constructor.
     * @param SubjectContract $subjectContract
     */
    public function __construct(SubjectContract $subjectContract)
    {
        $this->subjectContract = $subjectContract;
    }

    /**
     * Detach subjects from expeditions.
     *
     * @param $subjects
     * @param $id
     */
    public function detach($subjects, $id)
    {
        foreach ($subjects as $subject)
        {
            $array = [];
            foreach ($subject->expedition_ids as $value)
            {
                if ((int) $id !== (int) $value)
                {
                    $array[] = $value;
                }
            }
            $subject->expedition_ids = $array;
            $subject->save();
        }
    }

}