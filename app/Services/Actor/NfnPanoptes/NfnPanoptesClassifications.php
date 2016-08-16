<?php

namespace App\Services\Actor\NfnPanoptes;

ini_set('memory_limit', '1024M');

use App\Jobs\NfnClassificationsJob;
use App\Services\Actor\ActorInterface;
use App\Services\Actor\ActorService;
use App\Services\Api\NfnApi;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use RuntimeException;

ini_set('memory_limit', '1024M');

class NfnPanoptesClassifications implements ActorInterface
{

    use DispatchesJobs;

    /**
     * @var ActorService
     */
    private $service;

    /**
     * @var \App\Services\Actor\ActorRepositoryService
     */
    private $repoService;

    /**
     * NfnPanoptesClassifications constructor.
     *
     * @param ActorService $service
     */
    public function __construct(ActorService $service
    )
    {
        $this->service = $service;
        $this->repoService = $service->repositoryService;
    }

    /**
     * Process current state
     *
     * @param $actor
     * @return mixed|void
     * @throws \Exception
     */
    public function process($actor)
    {

        try
        {

            $record = $this->repoService->expedition->skipCache()->with(['project', 'stat'])->find($actor->pivot->expedition_id);

            $this->processExpeditionRecord($actor, $record);

            $this->service->processComplete($record);
        }
        catch (FileNotFoundException $e)
        {
        }
        catch (RuntimeException $e)
        {
        }
        catch (Exception $e)
        {
            $this->service->report->addError($e->getMessage());
            $this->service->report->reportSimpleError();
        }

    }

    /**
     * @param $actor
     * @param $record
     */
    private function processExpeditionRecord($actor, $record)
    {
        if ((int) $record->stat->transcriptsion_completed === 100)
        {
            $actor->pivot->queued = 0;
            ++$actor->pivot->state;
            $actor->completed = 1;
            $actor->pivot->save();
        }
        else
        {
            $this->dispatch((new NfnClassificationsJob($actor->pivot->expedition_id))
                ->onQueue($this->service->config->get('config.beanstalkd.job')));
            $actor->pivot->queued = 0;
            $actor->pivot->save();
        }
    }

}
