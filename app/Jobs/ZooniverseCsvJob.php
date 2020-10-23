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
     * @var bool
     */
    private $delayed;

    /**
     * @var int
     */
    private $tries;

    /**
     * Create a new job instance.
     *
     * @param array $expeditionIds
     * @param int $tries
     * @param bool $delayed
     */
    public function __construct(array $expeditionIds = [], int $tries = 0, bool $delayed = false)
    {
        $this->onQueue(config('config.classification_tube'));
        $this->expeditionIds = collect($expeditionIds);
        $this->delayed = $delayed;
        $this->tries = $tries;
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
                $service->createCsvRequest($expeditionId);
                $this->tries++;
                $this->dispatchJob($filteredIds->prepend($expeditionId)->toArray(), $this->tries, true);
                $this->delete();

                return;
            }

            $uri = $service->checkCsvRequest($expeditionId);
            if (! isset($uri)) {
                if($this->tries === 3) {
                    $this->dispatchJob($filteredIds->toArray());
                    throw new \Exception(t('Zooniverse csv creation for Expedition Id %s failed', $expeditionId));
                }

                $this->tries++;
                $this->dispatchJob($filteredIds->prepend($expeditionId)->toArray(), $this->tries, true);
                $this->delete();

                return;
            }

            ZooniverseCsvDownloadJob::withChain([
                new ZooniverseReconcileJob($expeditionId),
                new ZooniverseTranscriptionJob($expeditionId),
                new ZooniversePusherJob($expeditionId)
            ])->dispatch($expeditionId, $uri);

            if ($filteredIds->isNotEmpty()) {
                $this->dispatchJob($filteredIds->toArray());
            }

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

    /**
     * Dispatch job again.
     *
     * @param array $expeditionIds
     * @param int $tries
     * @param bool $delay
     */
    private function dispatchJob(array $expeditionIds, int $tries = 0, bool $delay = false)
    {
        $delay ? ZooniverseCsvJob::dispatch($expeditionIds, $tries, $delay)->delay(now()->addMinutes(3))
            : ZooniverseCsvJob::dispatch($expeditionIds, $tries, $delay);
    }
}
