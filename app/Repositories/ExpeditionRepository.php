<?php namespace App\Repositories;

use App\Models\Subject;
use App\Models\ExpeditionStat;
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

        $stat = new ExpeditionStat([
            'subject_count' => $attributes['subjectCount'],
            'transcriptions_total' => transcriptions_total($attributes['subjectCount']),
        ]);

        $expedition->stat()->save($stat);

        return $expedition;
    }

    public function update(array $attributes, $id)
    {
        $expedition = $this->model->find($id);
        $expedition->fill($attributes);
        $expedition->save();

        $subjects = $expedition->subjects()->get();
        $existingSubjectIds = [];
        foreach ($subjects as $subject) {
            $existingSubjectIds[] = $subject->_id;
        }

        $subjectModel = new Subject();
        $subjectModel->detachSubjects($existingSubjectIds, $expedition->id);

        $subjectIds = explode(',', $attributes['subjectIds']);
        $expedition->subjects()->attach($subjectIds);

        $expeditionStat = new ExpeditionStat();
        $stat = $expeditionStat->firstOrCreate(['expedition_id' => $expedition->id]);
        $stat->subject_count = $attributes['subjectCount'];
        $stat->transcriptions_total = transcriptions_total($attributes['subjectCount']);
        $stat->transcriptions_completed = transcriptions_completed($expedition->id);
        $stat->percent_completed = transcriptions_percent_completed($stat->transcriptions_total, $stat->transcriptions_completed);
        $stat->save();

        return $expedition;
    }
}
