<?php namespace App\Repositories;

use App\Models\Expedition as Model;
use App\Models\ExpeditionStat;
use App\Models\Subject;
use App\Repositories\Contracts\Expedition;
use Symfony\Component\Console\Helper\Helper;

class ExpeditionRepository extends Repository implements Expedition
{
    /**
     * @var \Expedition
     */
    protected $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Override parent create to allow sync
     *
     * @param array $data
     * @return mixed|void
     */
    public function create($data = [])
    {
        $expedition = $this->model->create($data);
        $subjects = explode(',', $data['subjectIds']);
        $expedition->subjects()->sync($subjects);

        $count = count($subjects);
        $stat = new ExpeditionStat([
            'subject_count' => $count,
            'transcription_total' => transcriptions_total($count),
        ]);

        $expedition->stat()->save($stat);

        return $expedition;
    }

    public function update($data = [])
    {
        $expedition = $this->model->find($data['id']);
        $expedition->fill($data);
        $expedition->save();

        $subjects = $expedition->subjects()->get();
        $existingSubjectIds = [];
        foreach ($subjects as $subject) {
            $existingSubjectIds[] = $subject->_id;
        }

        $subjectModel = new Subject();
        $subjectModel->detachSubjects($existingSubjectIds, $expedition->id);

        $subjectIds = explode(',', $data['subjectIds']);
        $expedition->subjects()->attach($subjectIds);

        $count = count($subjectIds);

        $expeditionStat = new ExpeditionStat();
        $stat = $expeditionStat->firstOrCreate(['expedition_id' => $expedition->id]);
        $stat->subject_count = $count;
        $stat->transcriptions_total = transcriptions_total($count);
        $stat->transcriptions_completed = transcriptions_completed($expedition->id);
        $stat->percent_completed = transcriptions_percent_completed($stat->transcriptions_total, $stat->transcriptions_completed);
        $stat->save();

        return $expedition;
    }

    /**
     * Find by uuid
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        return $this->model->findByUuid($uuid);
    }

    public function getAllExpeditions($id)
    {
        return $this->model->getAllExpeditions($id);
    }
}
