<?php

namespace App\Services\Model;

use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\SubjectContract;

class SubjectService
{

    /**
     * @var SubjectContract
     */
    public $subjectContract;

    /**
     * @var ExpeditionContract
     */
    public $expeditionContract;

    /**
     * SubjectService constructor.
     * @param SubjectContract $subjectContract
     * @param ExpeditionContract $expeditionContract
     */
    public function __construct(SubjectContract $subjectContract, ExpeditionContract $expeditionContract)
    {
        $this->subjectContract = $subjectContract;
        $this->expeditionContract = $expeditionContract;
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

    /**
     * Delete subjects. Check if expedition is assigned and if workflow is in process.
     *
     * @param $ids
     */
    public function deleteSubjects($ids)
    {
        $subjects = $this->subjectContract->setCacheLifetime(0)->findWhereIn(['_id', $ids]);

        $subjects->filter(function ($subject) {
            foreach ($subject->expedition_ids as $expeditionId)
            {
                $expedition = $this->expeditionContract->setCacheLifetime(0)->has('workflowManager')->find($expeditionId);
                return $expedition === null ?: false;
            }

            return true;
        })->each(function ($subject) {
            $this->subjectContract->delete($subject->_id);
        });
    }

}