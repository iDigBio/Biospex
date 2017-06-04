<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\ActorException;
use App\Exceptions\BiospexException;
use App\Services\Actor\ActorImageService;
use App\Services\Actor\ActorRepositoryService;
use App\Services\Actor\ActorServiceConfig;
use App\Services\File\FileService;
use App\Models\Actor;
use App\Models\StagedQueue;
use Exception;
use App\Services\Report\Report;
use App\Listeners\StagedQueueEventListener;
use PharData;


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
    private $stages;

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
        $this->actorRepositoryService = $actorRepositoryService;
        $this->actorImageService = $actorImageService;
        $this->fileService = $fileService;
        $this->report = $report;

        $this->stages = [
            'retrieve',
            'convert',
            'csv',
            'compress',
            'report',
        ];

        $this->nfnCsvMap = config('config.nfnCsvMap');
        $this->config = $config;
    }

    /**
     * Queue jobs for exports.
     *
     * @param Actor $actor
     * @see NfnPanoptes::actor() To set actor for this method.
     * @see StagedQueueEventListener::stagedQueueSaved() Event fired when queues saved.
     */
    public function stagedQueue(Actor $actor)
    {
        $attributes = [
            'expedition_id' => $actor->pivot->expedition_id,
            'actor_id'      => $actor->id,
            'stage'         => 0
        ];
        $this->actorRepositoryService->createStagedQueue($attributes);
    }

    /**
     * Process export.
     *
     * @param StagedQueue $queue
     * @see NfnPanoptes::queue() Directs queud job to determine what stage to run.
     */
    public function queue(StagedQueue $queue)
    {
        try
        {
            $this->config->setProperties($queue);
            $this->{[$queue->stage]}();
        }
        catch (BiospexException $e)
        {
            $this->report->addError($e->getMessage());
            $this->report->reportError();
            $attributes = ['queued' => 0, 'error' => 1];
            $this->actorRepositoryService->updateStagedQueue($queue->id, $attributes);
        }
    }

    /**
     * Retrieves images.
     *
     * @throws ActorException
     */
    public function retrieve()
    {
        $this->actorRepositoryService->setActorServiceConfig($this->config);
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId();
        if ($subjects->isEmpty())
        {
            throw new ActorException('Missing export subjects for Expedition ID ' . $this->config->expedition->id);
        }

        $this->config->setSubjects($subjects);

        $this->actorImageService->setActorServiceConfig($this->config);
        $this->actorImageService->getImages();
        $this->actorImageService->fireResetActorEvent();

        $this->advanceQueue();
    }

    /**
     * Convert image stage.
     *
     * @throws ActorException
     */
    public function convert()
    {
        try
        {
            $existingFiles = $this->getExistingFileArray();
            $files = collect($this->fileService->filesystem->files($this->config->workingDirectory));
            $this->config->setSubjects($files);
            $this->actorRepositoryService->setActorServiceConfig($this->config);

            $files->reject(function ($file) use ($existingFiles)
            {
                return $existingFiles->contains($this->fileService->filesystem->name($file));
            })->each(function ($file)
            {
                $fileName = $this->fileService->filesystem->name($file);
                $this->actorImageService->writeImagickFile($file, $fileName);
                $this->config->archivePhar->addFile($this->config->tmpDirectory . '/' . $fileName . '.jpg', $fileName . '.jpg');
            });

            $this->actorImageService->fireResetActorEvent();
            $this->advanceQueue();
        }
        catch (Exception $e)
        {
            throw new ActorException($e);
        }
    }

    /**
     * Create csv file.
     *
     * @throws ActorException
     */
    public function csv()
    {
        $this->actorRepositoryService->setActorServiceConfig($this->config);
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId();
        if ($subjects->isEmpty())
        {
            throw new ActorException('Missing export subjects for Expedition ' . $this->config->expedition->id);
        }

        $existingFiles = $this->getExistingFileArray();
        $this->config->setSubjects($subjects);

        $csvExport = $subjects->reject(function ($subject) use ($existingFiles)
        {
            return $existingFiles->contains($subject->_id . '.jpg');
        })->map(function ($subject)
        {
            $this->actorRepositoryService->fireActorProcessedEvent();

            return $this->mapNfnCsvColumns($subject);
        });

        if ( ! $this->createCsv($csvExport->toArray()))
        {
            throw new ActorException('Could not create CSV file for Expedition ID ' . $this->config->expedition->id . ' export');
        }

        $this->config->archivePhar->addFile($this->csvFilePath, $this->csvFileName);

        $this->actorRepositoryService->fireResetActorEvent();
        $this->advanceQueue();
    }

    /**
     * Compress and move.
     */
    public function compress()
    {
        $this->config->archivePhar->compress(\Phar::GZ); // copies to /path/to/my.tar.gz
        $this->fileService->filesystem->move($this->config->archiveTarGzPath, $this->config->archiveExportPath);

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
    public function report()
    {
        $this->actorRepositoryService->setActorServiceConfig($this->config);
        $project = $this->actorRepositoryService->getProjectGroupById();
        $this->fileService->filesystem->deleteDirectory($this->config->workingDirectory);

        $this->sendReport($project);

        $this->actorImageService->fireStateActorEvent();

        $this->advanceQueue();
    }

    /**
     * Advance the queue to the next stage.
     */
    private function advanceQueue()
    {
        $attributes = [
            'stage'   => $this->config->queue->stage+1,
            'missing' => array_merge($this->config->queue->missing, $this->actorImageService->getMissingImages())
        ];
        $this->actorRepositoryService->updateStagedQueue($this->config->queue->id, $attributes);
    }

    /**
     * Retrieve any existing files in phar archive.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getExistingFileArray()
    {
        $existingFiles = collect($this->config->archivePhar)->map(function ($file){
            return $file->getBaseName('.jpg');
        });

        return $existingFiles;
    }

    /**
     * Map nfn csvExport values from configuration.
     *
     * @param $subject
     * @return array
     */
    public function mapNfnCsvColumns($subject)
    {
        'nfnCsvMap' => [
        'subjectId'        => '_id',
        'imageName'        => '_id',
        'imageURL'         => 'accessURI',
        'references'       => ['occurrence' => 'references'],
        'scientificName'   => ['occurrence' => 'scientificName'],
        'country'          => ['occurrence' => 'country'],
        'stateProvince'    => ['occurrence' => 'stateProvince'],
        'county'           => ['occurrence' => 'county'],
        'eol'              => ['occurrence' => 'scientificName'],
        'mol'              => ['occurrence' => 'scientificName'],
        'idigbio'          => ['occurrence' => 'scientificName'],
        '#institutionCode' => ['occurrence' => 'institutionCode'],
        '#collectionCode'  => ['occurrence' => 'collectionCode'],
        '#catalogNumber'   => ['occurrence' => 'catalogNumber'],
        '#recordId'        => ['occurrence' => 'recordId'],
        '#expeditionId'    => '',
        '#expeditionTitle' => '',
        ];

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
