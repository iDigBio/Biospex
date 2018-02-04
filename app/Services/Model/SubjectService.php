<?php

namespace App\Services\Model;

use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Subject;

class SubjectService
{

    /**
     * @var Subject
     */
    public $subjectContract;

    /**
     * @var Expedition
     */
    public $expeditionContract;

    /**
     * SubjectService constructor.
     * @param Subject $subjectContract
     * @param Expedition $expeditionContract
     */
    public function __construct(Subject $subjectContract, Expedition $expeditionContract)
    {
        $this->subjectContract = $subjectContract;
        $this->expeditionContract = $expeditionContract;
    }

    /**
     * Delete subjects. Check if expedition is assigned and if workflow is in process.
     *
     * @param $subjectIds
     */
    public function deleteSubjects($subjectIds)
    {
        $subjects = $this->subjectContract->getWhereIn('_id', $subjectIds);

        $subjects->reject(function ($subject) {
            foreach ($subject->expedition_ids as $expeditionId)
            {
                $expedition = $this->expeditionContract->findExpeditionHavingWorkflowManager($expeditionId);
                if ($expedition !== null)
                    return true;
            }

            return false;
        })->each(function ($subject) {
            $this->subjectContract->delete($subject->_id);
        });
    }

}