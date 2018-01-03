<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Notifications\NfnExportComplete;
use App\Notifications\NfnExportError;
use App\Services\Actor\ActorImageService;
use App\Services\Actor\ActorRepositoryService;
use App\Services\Actor\ActorServiceConfig;
use App\Services\File\FileService;
use App\Services\Csv\Csv;
use App\Models\Actor;
use App\Models\ExportQueue;
use Illuminate\Support\Collection;

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
     * @var ActorServiceConfig
     */
    private $config;

    /**
     * @var mixed
     */
    public $stage = [
        'retrieveImages',
        'convertImages',
        'deleteOriginalImages',
        'buildCsv',
        'tarImages',
        'compressImages',
        'emailReport',
    ];

    /**
     * NfnPanoptesExport constructor.
     *
     * @param ActorServiceConfig $config
     * @param ActorRepositoryService $actorRepositoryService
     * @param ActorImageService $actorImageService
     * @param FileService $fileService
     * @param Csv $csvService
     */
    public function __construct(
        ActorServiceConfig $config,
        ActorRepositoryService $actorRepositoryService,
        ActorImageService $actorImageService,
        FileService $fileService,
        Csv $csvService
    )
    {
        $this->config = $config;
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
     * @see ExportQueueEventListener::entityCreated() Event fired when queues saved.
     */
    public function exportQueue(Actor $actor)
    {
        try
        {
            $attributes = [
                'expedition_id' => $actor->pivot->expedition_id,
                'actor_id'      => $actor->id
            ];

            $this->actorRepositoryService->firstOrCreateExportQueue($attributes);
        }
        catch (\Exception $e)
        {
            $this->config->fireActorErrorEvent($actor);
        }
    }

    /**
     * Process export.
     *
     * @param ExportQueue $queue
     * @see NfnPanoptes::queue() Directs queud job to determine what stage to run.
     */
    public function queue(ExportQueue $queue)
    {
        try
        {
            $this->config->setProperties($queue);
            $this->actorRepositoryService->setActorServiceConfig($this->config);
            $this->actorImageService->setActorServiceConfig($this->config);

            $method = $this->stage[$queue->stage];
            $this->{$method}();
        }
        catch (\Exception $e)
        {
            $this->config->fireActorErrorEvent();

            $attributes = ['queued' => 0, 'error' => 1];
            $this->actorRepositoryService->updateExportQueue($attributes, $queue->id);

            $message = trans('errors.nfn_export_error', [
                'title'   => $this->config->expedition->title,
                'id'      => $this->config->expedition->id,
                'message' => $e->getMessage()
            ]);

            $this->config->owner->notify(new NfnExportError($message));
        }
    }

    /**
     * Retrieves images.
     *
     * @throws \Exception
     */
    public function retrieveImages()
    {
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId();
        if ($subjects->isEmpty())
        {
            throw new \Exception('Missing export subjects for Expedition ID ' . $this->config->expedition->id);
        }

        $this->config->setSubjects($subjects);

        $this->actorImageService->getImages();
        $this->config->fireActorQueuedEvent();

        $this->advanceQueue();
    }

    /**
     * Convert image stage.
     *
     * @throws \Exception
     */
    public function convertImages()
    {
        $existingFiles = $this->getExistingConvertedFiles();
        $files = collect($this->fileService->filesystem->files($this->config->workingDirectory));
        $this->config->setSubjects($files);

        $files->reject(function ($file) use ($existingFiles)
        {
            if ($this->checkConvertedFile($file, $existingFiles))
            {
                $this->config->fireActorProcessedEvent();

                return true;
            }

            return false;
        })->each(function ($file)
        {
            $fileName = $this->fileService->filesystem->name($file);
            $this->actorImageService->writeImagickFile($file, $fileName);
        });

        if (empty($this->fileService->filesystem->files($this->config->tmpDirectory)))
        {
            throw new \Exception('Missing converted images for Expedition ' . $this->config->expedition->id);
        }

        $this->config->fireActorQueuedEvent();
        $this->advanceQueue();
    }

    /**
     * Delete original files to save space on server.
     */
    public function deleteOriginalImages()
    {
        $files = collect($this->fileService->filesystem->files($this->config->workingDirectory));

        $files->each(function($file){
            $this->fileService->filesystem->delete($file);
        });

        $this->config->deleteScratchTmpDir();
        $this->config->fireActorQueuedEvent();
        $this->advanceQueue();
    }

    /**
     * Create csv file.
     *
     * @throws \Exception
     */
    public function buildCsv()
    {
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId();
        if ($subjects->isEmpty())
        {
            throw new \Exception('Missing export subjects for Expedition ' . $this->config->expedition->id);
        }

        $existingFiles = $this->getExistingConvertedFiles();
        $this->config->setSubjects($subjects);

        $csvExport = $subjects->filter(function ($subject) use ($existingFiles)
        {
            return $existingFiles->contains($subject->_id);
        })->map(function ($subject)
        {
            $this->config->fireActorProcessedEvent();

            return $this->mapNfnCsvColumns($subject);
        });

        if ( ! $this->createCsv($csvExport->toArray()))
        {
            throw new \Exception('Could not create CSV file for Expedition ID ' . $this->config->expedition->id . ' export');
        }

        $this->config->fireActorQueuedEvent();
        $this->advanceQueue();
    }

    /**
     * Create tar file.
     */
    public function tarImages()
    {
        $this->config->archivePhar->buildFromDirectory($this->config->tmpDirectory);

        $this->config->fireActorQueuedEvent();

        $this->advanceQueue();
    }

    /**
     * Compress and move.
     *
     * @throws \Exception
     */
    public function compressImages()
    {
        if ( ! $this->fileService->filesystem->exists($this->config->archiveTarGzPath))
        {
            $this->config->archivePhar->compress(\Phar::GZ); // copies to /path/to/my.tar.gz
        }

        if ( ! $this->fileService->filesystem->move($this->config->archiveTarGzPath, $this->config->archiveExportPath))
        {
            throw new \Exception('Could not move compressed file to export directory ' . $this->config->expedition->id . ' export');
        }

        $values = [
            'expedition_id' => $this->config->expedition->id,
            'actor_id' => $this->config->actor->id,
            'file' => $this->config->archiveTarGz
        ];
        $attributes = [
            'expedition_id' => $this->config->expedition->id,
            'actor_id' => $this->config->actor->id,
            'file' => $this->config->archiveTarGz
        ];

        $this->actorRepositoryService->updateOrCreateDownload($attributes, $values);
        $this->advanceQueue();
    }

    /**
     * Send notification and clean up directories.
     *
     * @throws \Exception
     */
    public function emailReport()
    {
        $this->fileService->filesystem->deleteDirectory($this->config->workingDirectory);
        $this->fileService->filesystem->delete($this->config->archiveTarPath);

        $this->notify();

        $this->config->fireActorStateEvent();

        $this->actorRepositoryService->deleteExportQueue($this->config->queue->id);

        $this->config->fireActorUnQueuedEvent();
    }

    /**
     * Advance the queue to the next stage.
     */
    private function advanceQueue()
    {
        $queueMissing = empty($this->config->queue->missing) ? [] : $this->config->queue->missing;

        $attributes = [
            'stage'   => $this->config->queue->stage+1,
            'missing' => array_merge($queueMissing, $this->actorImageService->getMissingImages())
        ];
        $this->actorRepositoryService->updateExportQueue($attributes, $this->config->queue->id);
    }

    /**
     * Retrieve any existing files in phar archive.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getExistingConvertedFiles()
    {
        $files = collect($this->fileService->filesystem->files($this->config->tmpDirectory));
        $existingFiles = $files->map(function ($file){
            return $this->fileService->filesystem->name($file);
        });

        return $existingFiles;
    }

    /**
     * Check if converted file exists and is under file size.
     *
     * @param $file
     * @param Collection $existingFiles
     * @return bool
     */
    private function checkConvertedFile($file, $existingFiles)
    {
        $tmpFile = $this->config->tmpDirectory . '/' . $this->fileService->filesystem->name($file) . '.jpg';
        $exists = $existingFiles->contains($this->fileService->filesystem->name($file));
        $fileSize = $exists && filesize($tmpFile) < 600000;

        return $exists && $fileSize;
    }

    /**
     * Map nfn csvExport values from configuration.
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
                $csvArray[$key] = $this->config->expedition->id;
                continue;
            }
            if ($key === '#expeditionTitle')
            {
                $csvArray[$key] = $this->config->expedition->title;
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
                        $csvArray[$key] = str_replace('SCIENTIFIC_NAME', rawurlencode($subject->{$doc}->{$value}), config('config.nfnSearch.' . $key) );
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

        $this->csvFileName = $this->config->expedition->uuid . '.csv';
        $this->csvFilePath = $this->config->tmpDirectory . '/' . $this->csvFileName;
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
            $this->config->expedition->title,
            trans('emails.expedition_export_complete_message', ['expedition' => $this->config->expedition->title])
        ];

        $csv = create_csv($this->config->queue->missing);

        $this->config->owner->notify(new NfnExportComplete($message, $csv));
    }
}
