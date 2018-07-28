<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\EventCsvExport;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Services\Csv\Csv;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EventTranscriptionExportCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var User
     */
    private $user;

    /**
     * @var
     */
    private $eventId;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param null $eventId
     */
    public function __construct(User $user, $eventId)
    {
        $this->user = $user;
        $this->eventId = $eventId;
        $this->onQueue(config('config.beanstalkd.default'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\Interfaces\Event $event
     * @param \App\Repositories\Interfaces\PanoptesTranscription $panoptesTranscription
     * @param Csv $csv
     * @return void
     */
    public function handle(
        Event $event,
        PanoptesTranscription $panoptesTranscription,
        Csv $csv
    )
    {
        try
        {
            $classificationIds = $event->getEventClassificationIds($this->eventId);

            $transcriptions = $classificationIds->map(function($id) use($panoptesTranscription) {
                $transcript = $panoptesTranscription->find($id);
                unset($transcript['_id']);
                return $transcript;
            });

            $file = $transcriptions->isEmpty() ? null : $this->setCsv($transcriptions, $csv);

            $this->user->notify(new EventCsvExport(trans('messages.event_export_csv_complete'), $file));
        }
        catch (\Exception $e)
        {
            $this->user->notify(new EventCsvExport(trans('messages.event_export_csv_error', ['error' => $e->getMessage()])));
        }
    }

    /**
     * @param $transcriptions
     * @param \App\Services\Csv\Csv $csv
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    private function setCsv($transcriptions, Csv $csv) {
        $first = $transcriptions->first()->toArray();
        $header = array_keys($first);

        $file = config('config.reports_dir') . '/' . str_random() . '.csv';
        $csv->writerCreateFromPath($file);
        $csv->insertOne($header);
        $csv->insertAll($transcriptions->toArray());

        return $file;
    }
}
