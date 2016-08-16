<?php namespace App\Repositories;

use App\Models\Subject;
use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class ExpeditionRepository extends Repository implements Expedition, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Expedition::class;
    }

    /**
     * Return all expeditions for given user id.
     * 
     * @param $id
     * @return mixed
     */
    public function getAllExpeditions($id)
    {
        return $this->model->getAllExpeditions($id);
    }

    /**
     * Override parent create to allow sync.
     * 
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $expedition = $this->model->create($attributes);
        $subjects = explode(',', $attributes['subjectIds']);
        $expedition->subjects()->sync($subjects);

        $values = [
            'subject_count' => $attributes['subjectCount'],
            'transcriptions_total' => transcriptions_total($attributes['subjectCount']),
        ];

        $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);

        return $expedition;
    }

    public function update(array $attributes, $id)
    {
        $expedition = $this->model->with(['subjects', 'nfnWorkflow', 'stat'])->find($id);
        $expedition->fill($attributes);
        $expedition->save();

        if ($attributes['workflow'] !== '')
        {
            $expedition->nfnWorkflow()->updateOrCreate(['expedition_id' => $expedition->id], [$attributes['workflow']]);
        }

        $existingSubjectIds = [];
        foreach ($expedition->subjects as $subject) {
            $existingSubjectIds[] = $subject->_id;
        }

        $subjectModel = new Subject();
        $subjectModel->detachSubjects($existingSubjectIds, $expedition->id);

        $subjectIds = explode(',', $attributes['subjectIds']);
        $expedition->subjects()->attach($subjectIds);

        $total = transcriptions_total($attributes['subjectCount']);
        $completed = transcriptions_completed($expedition->id);
        $values = [
            'subject_count' => $attributes['subjectCount'],
            'transcriptions_total' => $total,
            'transcriptions_completed' => $completed,
            'percent_completed' => transcriptions_percent_completed($total, $completed)
        ];
        $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);

        return $expedition;
    }
}
