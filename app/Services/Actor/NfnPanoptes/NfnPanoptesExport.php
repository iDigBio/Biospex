<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\ActorException;
use App\Exceptions\BiospexException;
use App\Services\Actor\ActorImageService;
use App\Services\Actor\ActorRepositoryService;
use App\Services\Actor\ActorServiceConfig;
use App\Services\File\FileService;
use App\Models\Actor;
use App\Models\ExportQueue;
use Exception;
use App\Services\Report\Report;
use App\Listeners\ExportQueueEventListener;
use Illuminate\Support\Collection;
use PDOException;

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
     * @var Report
     */
    private $report;

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
     * @param Report $report
     */
    public function __construct(
        ActorServiceConfig $config,
        ActorRepositoryService $actorRepositoryService,
        ActorImageService $actorImageService,
        FileService $fileService,
        Report $report
    )
    {
        $this->config = $config;
        $this->actorRepositoryService = $actorRepositoryService;
        $this->actorImageService = $actorImageService;
        $this->fileService = $fileService;
        $this->report = $report;

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
        catch (PDOException $e)
        {
            $this->config->fireActorErrorEvent($actor);
            $this->report->addError($e->getMessage());
            $this->report->reportError();
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
        catch (BiospexException $e)
        {
            $this->config->fireActorErrorEvent();

            $attributes = ['queued' => 0, 'error' => 1];
            $this->actorRepositoryService->updateExportQueue($queue->id, $attributes);

            $this->report->addError($e->getMessage());
            $this->report->reportError();
        }
    }

    /**
     * Retrieves images.
     *
     * @throws ActorException
     */
    public function retrieveImages()
    {
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId();
        if ($subjects->isEmpty())
        {
            throw new ActorException('Missing export subjects for Expedition ID ' . $this->config->expedition->id);
        }

        $this->config->setSubjects($subjects);

        $this->actorImageService->getImages();
        $this->config->fireActorQueuedEvent();

        $this->advanceQueue();
    }

    /**
     * Convert image stage.
     *
     * @throws ActorException
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
            throw new ActorException('Missing converted images for Expedition ' . $this->config->expedition->id);
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
     * @throws ActorException
     */
    public function buildCsv()
    {
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId();
        if ($subjects->isEmpty())
        {
            throw new ActorException('Missing export subjects for Expedition ' . $this->config->expedition->id);
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
            throw new ActorException('Could not create CSV file for Expedition ID ' . $this->config->expedition->id . ' export');
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
     */
    public function compressImages()
    {
        if ( ! $this->fileService->filesystem->exists($this->config->archiveTarGzPath))
        {
            $this->config->archivePhar->compress(\Phar::GZ); // copies to /path/to/my.tar.gz
        }

        if ( ! $this->fileService->filesystem->move($this->config->archiveTarGzPath, $this->config->archiveExportPath))
        {
            throw new ActorException('Could not move compressed file to export directory ' . $this->config->expedition->id . ' export');
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
     * Send report and clean up directories.
     */
    public function emailReport()
    {
        $project = $this->actorRepositoryService->getProjectGroupById();
        $this->fileService->filesystem->deleteDirectory($this->config->workingDirectory);
        $this->fileService->filesystem->delete($this->config->archiveTarPath);

        $this->sendReport($project);

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
        $this->actorRepositoryService->updateExportQueue($this->config->queue->id, $attributes);
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
     */
    private function createCsv($csvExport)
    {
        if (0 === count($csvExport))
        {
            return false;
        }

        $this->csvFileName = $this->config->expedition->uuid . '.csv';
        $this->csvFilePath = $this->config->tmpDirectory . '/' . $this->csvFileName;
        $this->report->csv->writerCreateFromPath($this->csvFilePath);
        $this->report->csv->insertOne(array_keys($csvExport[0]));
        $this->report->csv->insertAll($csvExport);

        return true;
    }

    /**
     * Send report for process completed.
     *
     * @param $project
     */
    protected function sendReport($project)
    {
        $vars = [
            'title'          => $this->config->expedition->title,
            'message'        => trans('emails.expedition_export_complete_message', ['expedition' => $this->config->expedition->title]),
            'groupId'        => $project->group->id,
            'attachmentName' => trans('emails.missing_images_attachment_name', ['recordId' => $this->config->expedition->id])
        ];

        $this->report->processComplete($vars, $this->config->queue->missing);
    }
}
