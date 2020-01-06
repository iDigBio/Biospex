<?php

namespace App\Repositories\Eloquent;

use App\Models\EventTranscription as Model;
use App\Repositories\Interfaces\EventTranscription;
use Illuminate\Support\Collection;

class EventTranscriptionRepository extends EloquentRepository implements EventTranscription
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getEventClassificationIds($eventId)
    {
        $ids = $this->model->where('event_id', $eventId)->pluck('classification_id');

        $this->resetModel();

        return $ids;
    }

    /**
     * @inheritDoc
     */
    public function getEventStepChartTranscriptions(string $eventId, string $startLoad, string $endLoad): ?Collection
    {
        $result = $this->model->with(['team:id,title'])
            ->selectRaw('event_id, ADDTIME(FROM_UNIXTIME(FLOOR((UNIX_TIMESTAMP(created_at))/300)*300), "0:05:00") AS time, team_id, count(id) as count')
            ->where('event_id', $eventId)
            ->where('created_at', '>=', $startLoad)
            ->where('created_at', '<', $endLoad)
            ->groupBy('time', 'team_id', 'event_id')->orderBy('time')->get();

        $this->resetModel();

        return $result;
    }
}