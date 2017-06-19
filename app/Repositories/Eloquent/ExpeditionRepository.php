<?php

namespace App\Repositories\Eloquent;

use App\Models\Expedition;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\SubjectContract;
use Illuminate\Contracts\Container\Container;


class ExpeditionRepository extends EloquentRepository implements ExpeditionContract
{

    /**
     * ExpeditionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Expedition::class)
            ->setRepositoryId('biospex.repository.expedition');
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsForNfnClassificationProcess(array $ids = [], array $attributes = ['*'])
    {
        $this->with(['nfnWorkflow', 'stat'])->has('nfnWorkflow')
            ->whereHas('actors', function ($query)
            {
                $query->where('completed', 0);
            }, '=');

        return empty($ids) ? $this->findAll($attributes) : $this->findWhereIn(['id', $ids]);
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionSubjectCounts($id)
    {
        return $this->find($id)->subjects()->count();
    }

    /**
     * @inheritdoc
     */
    public function expeditionsByUserId($userId, array $relations =[])
    {
        return $this->with($relations)
            ->findWhereHas(['project.group.users', function ($query) {
                $query->where('user_id', 1);
            }]);
    }

    /**
     * @inheritdoc
     */
    public function createExpedition(array $attributes = [], $syncRelations = false)
    {
        $expedition = $this->create($attributes);
        $subjects = explode(',', $attributes['subjectIds']);
        $expedition->subjects()->sync($subjects);

        $values = [
            'subject_count' => count($subjects),
            'transcriptions_total' => transcriptions_total(count($subjects)),
        ];

        $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);

        return $expedition;
    }

    /**
     * @inheritdoc
     */
    public function updateExpedition($id, array $attributes = [], $syncRelations = false)
    {
        $expedition = $this->with(['subjects', 'nfnWorkflow', 'stat'])->find($id);
        $expedition->fill($attributes);
        $expedition->save();

        if ($attributes['workflow'] !== '')
        {
            $values = [
                'project_id' => $attributes['project_id'],
                'expedition_id' => $expedition->id,
                'workflow' => $attributes['workflow']
            ];
            $expedition->nfnWorkflow()->updateOrCreate(['expedition_id' => $expedition->id], $values);
        }

        if ( ! isset($attributes['admin']))
        {
            $existingSubjectIds = [];
            foreach ($expedition->subjects as $subject) {
                $existingSubjectIds[] = $subject->_id;
            }

            $subjectContract = app(SubjectContract::class);
            $subjectContract->detachSubjects($existingSubjectIds, $expedition->id);

            $subjectIds = explode(',', $attributes['subjectIds']);
            $expedition->subjects()->attach($subjectIds);

            $total = transcriptions_total(count($subjectIds));
            $completed = transcriptions_completed($expedition->id);
            $values = [
                'subject_count' => count($subjectIds),
                'transcriptions_total' => $total,
                'transcriptions_completed' => $completed,
                'percent_completed' => transcriptions_percent_completed($total, $completed)
            ];
            $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);
        }

        $expedition = $this->with(['subjects', 'nfnWorkflow', 'stat'])->find($id);

        return $expedition;
    }
}
