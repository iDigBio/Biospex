<?php

namespace App\Jobs;

use App\Jobs\Traits\SkipNfn;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Csv\ZooniverseCsvService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ZooniverseCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * @var array
     */
    private $expeditionIds;

    /**
     * @var int|null
     */
    private $expeditionId;

    /**
     * @var bool
     */
    private $delayed;

    /**
     * Create a new job instance.
     *
     * @param array $expeditionIds
     * @param bool $delayed
     */
    public function __construct(array $expeditionIds = [], bool $delayed = false)
    {
        $this->onQueue(config('config.classification_tube'));
        $this->expeditionIds = collect($expeditionIds);
        $this->delayed = $delayed;
    }

    /**
     * Execute job.
     *
     * @param \App\Services\Csv\ZooniverseCsvService $service
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(ZooniverseCsvService $service)
    {
        $filteredIds = $this->expeditionIds->reject(function($id) {
            return $this->skipApi($id);
        });

        if ($filteredIds->isEmpty()) {
            $this->delete();

            return;
        }

        try {
            $expeditionId = $filteredIds->shift();

            if (! $this->delayed) {
                \Log::alert('Sending create request');
                $service->createCsvRequest($expeditionId);
                ZooniverseCsvJob::dispatch($filteredIds->prepend($expeditionId)->toArray(), true)->delay(now()->addMinutes(5));
                $this->delete();

                return;
            }

            $uri = $service->checkCsvRequest($expeditionId);
            if (! isset($uri)) {
                \Log::alert('Missing uri');
                ZooniverseCsvJob::dispatch($filteredIds->prepend($expeditionId)->toArray(), true)->delay(now()->addMinutes(5));
                $this->delete();

                return;
            }

            \Log::alert('Start chain');
            ZooniverseCsvDownloadJob::withChain([
                (new ZooniverseReconcileJob($expeditionId)),
                (new ZooniverseTranscriptionJob($expeditionId)),
                (new ZooniversePusherJob($expeditionId))
            ])->dispatch($expeditionId, $uri);

            if ($filteredIds->isNotEmpty()) {
                \Log::alert('Ids not empty');
                ZooniverseCsvJob::dispatch($filteredIds->toArray());
            }

            \Log::alert('Deleting');
            $this->delete();

            return;

        } catch (\Exception $e) {
            $user = User::find(1);
            $messages = [
                t('Error: %s', $e->getMessage()),
                t('File: %s', $e->getFile()),
                t('Line: %s', $e->getLine()),
            ];
            $user->notify(new JobError(__FILE__, $messages));
        }
    }
}
