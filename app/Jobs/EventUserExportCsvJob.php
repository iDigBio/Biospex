<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\EventCsvExport;
use App\Repositories\Interfaces\Event;
use App\Services\Csv\Csv;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EventUserExportCsvJob implements ShouldQueue
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
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param Csv $csv
     * @return void
     */
    public function handle(
        Event $eventContract,
        Csv $csv
    )
    {
        try
        {
            $event = $eventContract->getEventShow($this->eventId);
            $rows = $event->teams->flatMap(function ($team){
                return $team->users->map(function ($user) use ($team){
                    return [
                        $team->title,
                        $user->nfn_user,
                        $user->transcriptions_count
                    ];
                });
            })->toArray();

            $file = empty($rows) ? null : $this->setCsv($csv, $rows);

            $this->user->notify(new EventCsvExport(trans('messages.event_export_csv_complete'), $file));
        }
        catch (\Exception $e)
        {
            $this->user->notify(new EventCsvExport(trans('messages.event_export_csv_error', ['error' => $e->getMessage()])));
        }
    }

    /**
     * @param \App\Services\Csv\Csv $csv
     * @param $rows
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    private function setCsv(Csv $csv, $rows)
    {
        $file = \Storage::path(config('config.reports_dir') . '/' . str_random() . '.csv');
        $csv->writerCreateFromPath($file);
        $csv->insertOne(['Team', 'User', 'Transcriptions']);
        $csv->insertAll($rows);

        return $file;
    }
}
