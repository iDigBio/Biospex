<?php declare(strict_types=1);

namespace App\Services\Model;

use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventTeam;
use App\Repositories\Interfaces\EventTranscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EventStepChartService
{
    /**
     * @var \App\Repositories\Interfaces\Event
     */
    private $eventContract;

    /**
     * @var \App\Repositories\Interfaces\EventTranscription
     */
    private $eventTranscriptionContract;

    /**
     * @var \App\Repositories\Interfaces\EventTeam
     */
    private $eventTeamContract;

    /**
     * AjaxService constructor.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param \App\Repositories\Interfaces\EventTranscription $eventTranscriptionContract
     * @param \App\Repositories\Interfaces\EventTeam $eventTeamContract
     */
    public function __construct(
        Event $eventContract,
        EventTranscription $eventTranscriptionContract,
        EventTeam $eventTeamContract
    ) {
        $this->eventContract = $eventContract;
        $this->eventTranscriptionContract = $eventTranscriptionContract;
        $this->eventTeamContract = $eventTeamContract;
    }

    /**
     * Get event transcription data for step chart.
     *
     * @param string $eventId
     * @param string|null $timestamp
     * @return array
     */
    public function eventStepChart(string $eventId, string $timestamp = null): ?array
    {
        $event = $this->eventContract->findWith($eventId, ['teams']);
        if ($event === null) {
            return null;
        }

        $loadTime = $this->getLoadTime($event, $timestamp);

        $startLoad = $loadTime->copy()->format('Y-m-d H:i:s');
        $endLoad = $timestamp === null ?
            Carbon::now()->floorMinutes(5)->format('Y-m-d H:i:s') :
            $loadTime->addMinutes(5)->format('Y-m-d H:i:s');

        $intervals = $this->setTimeIntervals($event, $startLoad, $endLoad, $timestamp);

        $transcriptions = $this->eventTranscriptionContract->getEventStepChartTranscriptions($eventId, $startLoad, $endLoad);

        return $transcriptions === null ? $this->processEmptyResult($event, $intervals) : $this->processTranscriptionResult($event, $transcriptions, $intervals);
    }

    /**
     * Process an empty result set as this means it's very beginning.
     *
     * @param \App\Models\Event $event
     * @param \Illuminate\Support\Collection $intervals
     * @return array
     */
    public function processEmptyResult(\App\Models\Event $event, Collection $intervals): array
    {
        $teams = $event->teams->pluck('title');

        $data = [];
        $intervals->each(function ($interval, $key) use (&$data, $teams) {
            $teams->each(function ($team) use (&$data, $key) {
                $data[$key][$team->title] = 0;
            });
        });

        return $data;
    }

    /**
     * Process query data for results.
     *
     * @param \App\Models\Event $event
     * @param \Illuminate\Support\Collection $transcriptions
     * @param \Illuminate\Support\Collection $intervals
     * @return array
     */
    protected function processTranscriptionResult(
        \App\Models\Event $event,
        Collection $transcriptions,
        Collection $intervals
    ) {
        $mapped = $this->mapWithDateKeys($transcriptions, $event);

        $merged = $this->mergeIntervals($mapped, $intervals);

        $transformed = $this->addMissingTeamCount($event, $merged);

        return $this->setDateInArray($transformed);
    }

    /**
     * Map teams and data using keys.
     * Count is per hour (count * 12) for each 5 minutes.
     *
     * @param \Illuminate\Support\Collection $transcriptions
     * @param \App\Models\Event $event
     * @return \Illuminate\Support\Collection
     */
    protected function mapWithDateKeys(Collection $transcriptions, \App\Models\Event $event): Collection
    {
        $data = [];
        $transcriptions->each(function ($transcription) use (&$data, $event) {
            $date = Carbon::parse($transcription->time)->timezone($event->timezone)->format('Y-m-d H:i:s');
            $data[$date][$transcription->team->title] = $transcription->count * 12;
        });

        return collect($data);
    }

    /**
     * Merge mapped and intervals.
     *
     * @param \Illuminate\Support\Collection $mapped
     * @param \Illuminate\Support\Collection $intervals
     * @return \Illuminate\Support\Collection
     */
    protected function mergeIntervals(Collection $mapped, Collection $intervals): Collection
    {
        return $intervals->merge($mapped)->map(function ($collection) {
            return is_array($collection) ? $collection : [];
        });
    }

    /**
     * Add missing team counts. Set to 0.
     *
     * @param \App\Models\Event $event
     * @param \Illuminate\Support\Collection $merged
     * @return \Illuminate\Support\Collection
     */
    protected function addMissingTeamCount(\App\Models\Event $event, Collection $merged): Collection
    {
        $teams = collect($event->teams->pluck('title'));

        return $merged->transform(function ($collection, $key) use ($teams) {
            $teams->each(function ($team) use (&$collection, $key) {
                if (! isset($collection[$team])) {
                    $collection[$team] = 0;
                }
            });

            return $collection;
        });
    }

    /**
     * Set the date in the array and keys to numeric.
     *
     * @param \Illuminate\Support\Collection $transformed
     * @return array
     */
    protected function setDateInArray(Collection $transformed): array
    {
        $complete = $transformed->map(function ($collection, $key) {
            return array_merge($collection, ['date' => $key]);
        });

        return array_values($complete->toArray());
    }

    /**
     * Get the load time given.
     *
     * @param \App\Models\Event $event
     * @param string|null $timestamp
     * @return \Carbon\Carbon
     */
    protected function getLoadTime(\App\Models\Event $event, string $timestamp = null): \Carbon\Carbon
    {
        return $timestamp === null ?
            Carbon::parse($event->start_date) :
            Carbon::createFromTimestampMs($timestamp)->floorMinutes(5);
    }

    /**
     * Get 5 minute time intervals.
     *
     * @param \App\Models\Event $event
     * @param string $startLoad
     * @param string $endLoad
     * @param string|null $timestamp
     * @return \Illuminate\Support\Collection
     */
    protected function setTimeIntervals(\App\Models\Event $event, string $startLoad, string $endLoad, string $timestamp = null): Collection
    {
        $start = Carbon::parse($startLoad)->timezone($event->timezone);
        $end = Carbon::parse($endLoad)->timezone($event->timezone);

        do {
            $intervals[] = $timestamp == null ?
                $start->copy()->format('Y-m-d H:i:s') :
                $start->copy()->addMinutes(5)->format('Y-m-d H:i:s');
            $timestamp = false;
            $start->addMinutes(5);
        } while ($start->lt($end));

        return collect($intervals)->flip();
    }
}