<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Facades\GeneralHelper;
use App\Notifications\NfnExportComplete;
use App\Services\Actor\ActorImageService;
use App\Services\Actor\ActorRepositoryService;
use App\Services\File\FileService;
use App\Services\Csv\Csv;
use App\Models\Actor;
use App\Models\ExportQueue;

putenv('MAGICK_THREAD_LIMIT=1');

class NfnPanoptesExport
{

    /**
     * @var ActorRepositoryService
     */
    private $actorRepositoryService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var ActorImageService
     */
    private $actorImageService;

    /**
     * @var Csv
     */
    private $csvService;

    /**
     * @var mixed
     */
    private $nfnCsvMap;

    /**
     * @var
     */
    private $csvFileName;

    /**
     * @var
     */
    private $csvFilePath;

    /**
     * @var mixed
     */
    public $stage = [
        'retrieveImages',
        'convertImages',
        'deleteOriginalImages',
        'buildCsv',
        'tarImages',
        'emailReport',
    ];

    /**
     * NfnPanoptesExport constructor.
     *
     * @param ActorRepositoryService $actorRepositoryService
     * @param ActorImageService $actorImageService
     * @param FileService $fileService
     * @param Csv $csvService
     */
    public function __construct(
        ActorRepositoryService $actorRepositoryService,
        ActorImageService $actorImageService,
        FileService $fileService,
        Csv $csvService
    )
    {
        $this->actorRepositoryService = $actorRepositoryService;
        $this->actorImageService = $actorImageService;
        $this->fileService = $fileService;
        $this->csvService = $csvService;

        $this->nfnCsvMap = config('config.nfnCsvMap');
    }

    /**
     * Queue jobs for exports.
     *
     * @param Actor $actor
     * @see NfnPanoptes::actor() To set actor for this method.
     * @see ExportQueueEventListener::created() Event fired when queues saved.
     */
    public function exportQueue(Actor $actor)
    {
        $attributes = [
            'expedition_id' => $actor->pivot->expedition_id,
            'actor_id'      => $actor->id
        ];

        $this->actorRepositoryService->firstOrCreateExportQueue($attributes);
    }

    /**
     * Process export.
     *
     * @param ExportQueue $queue
     * @see NfnPanoptes::queue() Directs queud job to determine what stage to run.
     */
    public function queue(ExportQueue $queue)
    {
        $this->actorImageService->setProperties($queue);
        $method = $this->stage[$queue->stage];
        $this->{$method}();
    }

    /**
     * Retrieves images.
     *
     * @throws \Exception
     */
    public function retrieveImages()
    {
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId($this->actorImageService->expedition->id);
        if ($subjects->isEmpty())
        {
            throw new \Exception('Missing export subjects for Expedition ID ' . $this->actorImageService->expedition->id);
        }

        $this->actorImageService->setSubjects($subjects);
        $this->actorImageService->getImages();
        $this->actorImageService->fireActorQueuedEvent();

        $this->advanceQueue();

        return;
    }

    /**
     * Convert image stage.
     *
     * @throws \Exception
     */
    public function convertImages()
    {
        $files = collect($this->fileService->filesystem->files($this->actorImageService->workingDirectory));
        $this->actorImageService->setSubjects($files);

        $files->reject(function ($file) {
            return $this->checkConvertedFile($file);
        })->each(function ($file) {
            $fileName = $this->fileService->filesystem->name($file);
            $this->actorImageService->processFileImage($file, $fileName);
            $this->actorImageService->fireActorProcessedEvent();
        });

        if (empty($this->fileService->filesystem->files($this->actorImageService->tmpDirectory)))
        {
            $this->emailReport();

            return;
        }

        $this->actorImageService->fireActorQueuedEvent();
        $this->advanceQueue();

        return;
    }

    /**
     * Delete original files to save space on server.
     */
    public function deleteOriginalImages()
    {
        $files = collect($this->fileService->filesystem->files($this->actorImageService->workingDirectory));

        $files->each(function ($file) {
            $this->fileService->filesystem->delete($file);
        });

        $this->actorImageService->deleteScratchTmpDir();
        $this->actorImageService->fireActorQueuedEvent();
        $this->advanceQueue();

        return;
    }

    /**
     * Create csv file.
     *
     * @throws \Exception
     */
    public function buildCsv()
    {
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId($this->actorImageService->expedition->id);
        if ($subjects->isEmpty())
        {
            throw new \Exception('Missing export subjects for Expedition ' . $this->actorImageService->expedition->id);
        }

        $this->actorImageService->setSubjects($subjects);

        $csvExport = $subjects->filter(function ($subject) {
            return $this->checkConvertedFile($subject->_id, true);
        })->map(function ($subject) {
            $this->actorImageService->fireActorProcessedEvent();

            return $this->mapNfnCsvColumns($subject);
        });

        if ( ! $this->createCsv($csvExport->toArray()))
        {
            throw new \Exception('Could not create CSV file for Expedition ID ' . $this->actorImageService->expedition->id . ' export');
        }

        $this->actorImageService->fireActorQueuedEvent();
        $this->advanceQueue();

        return;
    }

    /**
     * Create tar file.
     *
     * @throws \Exception
     */
    public function tarImages()
    {
        exec("tar -czf {$this->actorImageService->archiveTarGzPath} {$this->actorImageService->tmpDirectory}", $out, $ok);

        if ( ! $ok) {
            $values = [
                'expedition_id' => $this->actorImageService->expedition->id,
                'actor_id'      => $this->actorImageService->actor->id,
                'file'          => $this->actorImageService->archiveTarGz,
                'type'          => 'export'
            ];
            $attributes = [
                'expedition_id' => $this->actorImageService->expedition->id,
                'actor_id'      => $this->actorImageService->actor->id,
                'file'          => $this->actorImageService->archiveTarGz,
                'type'          => 'export'
            ];

            $this->actorRepositoryService->updateOrCreateDownload($attributes, $values);

            $this->advanceQueue();

            return;
        }

        throw new \Exception('Could not create compressed export file for Expedition: ' . $this->actorImageService->expedition->id);
    }

    /**
     * Send notification and clean up directories.
     *
     * @throws \Exception
     */
    public function emailReport()
    {
        $this->fileService->filesystem->deleteDirectory($this->actorImageService->workingDirectory);

        $this->notify();

        $this->actorImageService->fireActorStateEvent();

        $this->actorRepositoryService->deleteExportQueue($this->actorImageService->queue->id);

        $this->actorImageService->fireActorUnQueuedEvent();

        return;
    }

    /**
     * Advance the queue to the next stage.
     */
    private function advanceQueue()
    {
        $queueMissing = empty($this->actorImageService->queue->missing) ? [] : $this->actorImageService->queue->missing;

        $attributes = [
            'stage'   => $this->actorImageService->queue->stage + 1,
            'missing' => array_merge($queueMissing, $this->actorImageService->getMissingImages())
        ];

        $this->actorRepositoryService->updateExportQueue($attributes, $this->actorImageService->queue->id);

        return;
    }

    /**
     * Check if converted file exists and is under file size.
     *
     * @param $file
     * @param bool $subject used if passing a subject id as file
     * @return bool
     */
    private function checkConvertedFile($file, $subject = false)
    {
        $fileName = ! $subject ? $this->fileService->filesystem->name($file) : $file;
        $tmpFile = $this->actorImageService->tmpDirectory . '/' . $fileName . '.jpg';
        if ($this->actorImageService->checkFileExists($tmpFile))
        {
            $this->actorImageService->fireActorProcessedEvent();

            return true;
        }

        return false;
    }

    /**
     * Map nfn csvExport values from actorImageServiceuration.
     *
     * @param $subject
     * @return array
     */
    public function mapNfnCsvColumns($subject)
    {
        $csvArray = [];
        foreach ($this->nfnCsvMap as $key => $item)
        {
            if ($key === '#expeditionId')
            {
                $csvArray[$key] = $this->actorImageService->expedition->id;
                continue;
            }
            if ($key === '#expeditionTitle')
            {
                $csvArray[$key] = $this->actorImageService->expedition->title;
                continue;
            }
            if ($key === 'imageName')
            {
                $csvArray[$key] = $subject->_id . '.jpg';
                continue;
            }
            if ( ! is_array($item))
            {
                $csvArray[$key] = $item === '' ? '' : $subject->{$item};
                continue;
            }

            $csvArray[$key] = '';
            foreach ($item as $doc => $value)
            {
                if (isset($subject->{$doc}->{$value}))
                {
                    if ($key === 'eol' || $key === 'mol' || $key === 'idigbio')
                    {
                        $csvArray[$key] = str_replace('SCIENTIFIC_NAME', rawurlencode($subject->{$doc}->{$value}), config('config.nfnSearch.' . $key));
                        break;
                    }

                    $csvArray[$key] = $subject->{$doc}->{$value};
                    break;
                }
            }
        }

        return $csvArray;
    }

    /**
     * Create csv file.
     *
     * @param $csvExport
     * @return bool
     * @throws \League\Csv\CannotInsertRecord
     * @throws \TypeError
     */
    private function createCsv($csvExport)
    {
        if (0 === count($csvExport))
        {
            return false;
        }

        $this->csvFileName = $this->actorImageService->expedition->uuid . '.csv';
        $this->csvFilePath = $this->actorImageService->tmpDirectory . '/' . $this->csvFileName;
        $this->csvService->writerCreateFromPath($this->csvFilePath);
        $this->csvService->insertOne(array_keys($csvExport[0]));
        $this->csvService->insertAll($csvExport);

        return true;
    }

    /**
     * Send notify for process completed.
     *
     * @throws \Exception
     */
    protected function notify()
    {
        $message = [
            $this->actorImageService->expedition->title,
            trans('messages.expedition_export_complete_message', ['expedition' => $this->actorImageService->expedition->title])
        ];

        $csvPath = storage_path('app/reports/'. md5($this->actorImageService->queue->id) . '.csv');
        $csv = GeneralHelper::createCsv($this->actorImageService->queue->missing, $csvPath);

        $this->actorImageService->owner->notify(new NfnExportComplete($message, $csv));
    }
}
