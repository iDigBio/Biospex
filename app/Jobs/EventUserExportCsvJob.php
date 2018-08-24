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
     * @var
     */
    private $rows;

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
            $event->teams->each(function($team){
                foreach ($team->users as $user)
                {
                    $this->rows[] = [$team->title, $user->nfn_user, $user->transcriptionCount];
                }
            });

            $file = empty($this->rows) ? null : $this->setCsv($csv);

            $this->user->notify(new EventCsvExport(trans('messages.event_export_csv_complete'), $file));
        }
        catch (\Exception $e)
        {
            $this->user->notify(new EventCsvExport(trans('messages.event_export_csv_error', ['error' => $e->getMessage()])));
        }
    }

    /**
     * @param \App\Services\Csv\Csv $csv
     * @throws \League\Csv\CannotInsertRecord
     */
    private function setCsv(Csv $csv)
    {
        $file = config('config.reports_dir') . '/' . str_random() . '.csv';
        $csv->writerCreateFromPath($file);
        $csv->insertOne(['Team', 'User', 'Transcriptions']);
        $csv->insertAll($this->rows);
    }
}
