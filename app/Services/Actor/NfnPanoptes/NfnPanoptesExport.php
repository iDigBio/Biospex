<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\BiospexException;
use App\Services\Actor\ActorInterface;
use App\Services\Actor\ActorService;

class NfnPanoptesExport implements ActorInterface
{

    /**
     * @var ActorService
     */
    private $service;

    /**
     * @var \App\Services\File\fileService
     */
    private $fileService;

    /**
     * @var \App\Services\Actor\ActorImageService
     */
    private $actorImageService;

    /**
     * @var \App\Services\Actor\ActorRepositoryService
     */
    private $actorRepoService;

    /**
     * @var
     */
    private $record;

    /**
     * @var array
     */
    public $csvExport = [];

    /**
     * @var mixed
     */
    public $nfnExportDir;

    /**
     * @var mixed
     */
    public $nfnCsvMap;

    /**
     * @var mixed
     */
    public $largeWidth;

    /**
     * NfnPanoptesExport constructor.
     * @param ActorService $service
     * @internal param ActorRepositoryService $repositoryService
     * @internal param ActorImageService $actorImageService
     */
    public function __construct(
        ActorService $service
    )
    {
        $this->service = $service;
        $this->fileService = $service->fileService;
        $this->actorImageService = $service->actorImageService;
        $this->actorRepoService = $service->actorRepoService;

        $this->nfnExportDir = $this->service->config->get('config.nfn_export_dir');
        $this->nfnCsvMap = $this->service->config->get('config.nfnCsvMap');
        $this->largeWidth = $this->service->config->get('config.images.nfnLrgWidth');
    }

    /**
     * Process current state
     *
     * @param $actor
     * @return mixed|void
     * @throws BiospexException
     */
    public function process($actor)
    {
        try
        {
            $this->fileService->makeDirectory($this->nfnExportDir);

            $this->record = $this->actorRepoService->expedition
                ->skipCache()
                ->with(['project.group.owner', 'subjects'])
                ->find($actor->pivot->expedition_id);

            \Log::alert('retrieved record');

            $this->service->setWorkingDirectory("{$actor->id}-{$this->record->uuid}");
            $tempDir = "{$this->service->workingDir}/{$actor->id}-{$this->record->uuid}";
            $this->fileService->makeDirectory($tempDir);
            \Log::alert('created directories');

            \Log::alert('getImages');
            $this->actorImageService->testImages($this->record->subjects, $tempDir, $actor);
            return;

            $this->buildCsvArray($this->record->subjects, $tempDir);

            if ($this->createCsv($tempDir))
            {
                $tarGzFiles = $this->fileService->compressDirectories($this->service->workingDir, $this->nfnExportDir);
                $this->actorRepoService->createDownloads($this->record->id, $actor->id, $tarGzFiles);
            }

            //$this->fileService->filesystem->deleteDirectory($this->service->workingDir);

            $actor->pivot->queued = 0;
            $actor->pivot->state++;
            $actor->pivot->save();

            //$this->sendReport();
        }
        catch (BiospexException $e)
        {
            $actor->pivot->queued = 0;
            $actor->pivot->error = 1;
            $actor->pivot->save();

            $this->service->report->addError(trans('errors.nfn_classifications_error', [
                'title'   => $this->record->title,
                'id'      => $this->record->id,
                'message' => $e->getMessage()
            ]));

            $this->service->report->reportError($this->record->project->group->owner->email);

            $this->service->handler->report($e);
        }

    }

    /**
     * Build csvExport array for export.
     *
     * @param array $subjects
     * @param $tempDir
     */
    public function buildCsvArray($subjects, $tempDir)
    {
        foreach ($subjects as $subject)
        {
            $file = $tempDir . '/' . $subject->_id . '.jpg';
            if ($this->fileService->checkFileExists($file))
            {
                $this->csvExport[] = $this->mapNfnCsvColumns($subject);
            }
            else
            {
                $this->actorImageService->setMissingImages($subject, 'Converted image did not exist');
            }
        }
    }

    /**
     * Map nfn csvExport values from configuration
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
                $csvArray[$key] = $this->record->id;
                continue;
            }
            if ($key === '#expeditionTitle')
            {
                $csvArray[$key] = $this->record->title;
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
                        $csvArray[$key] = str_replace('SCIENTIFIC_NAME', rawurlencode($subject->{$doc}->{$value}), $this->service->config->get('config.nfnSearch.' . $key) );
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
     * @param $tempDir
     * @return bool
     */
    public function createCsv($tempDir)
    {
        if (0 === count($this->csvExport))
        {
            return false;
        }

        $this->service->report->csv->writerCreateFromPath($tempDir . '/' . $this->record->uuid . '.csv');
        $this->service->report->csv->insertOne(array_keys($this->csvExport[0]));
        $this->service->report->csv->insertAll($this->csvExport);

        return true;
    }

    /**
     * Send report for process completed.
     */
    protected function sendReport()
    {
        $vars = [
            'title'          => $this->record->title,
            'message'        => trans('emails.expedition_export_complete_message', ['expedition' => $this->record->title]),
            'groupId'        => $this->record->project->group->id,
            'attachmentName' => trans('emails.missing_images_attachment_name', ['recordId' => $this->record->id])
        ];

        $this->service->processComplete($vars, $this->actorImageService->getMissingImages());
    }
}
