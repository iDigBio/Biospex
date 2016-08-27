<?php

namespace App\Services\Actor\NfnPanoptes;

ini_set('memory_limit', '1024M');

use App\Services\Actor\ActorInterface;
use App\Services\Actor\ActorService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Exception;
use RuntimeException;

class NfnPanoptesExport implements ActorInterface
{

    /**
     * @var ActorService
     */
    private $service;

    /**
     * @var \App\Services\Actor\ActorFileService
     */
    private $actorFileService;

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
     * Missing image when retrieving via curl.
     *
     * @var array
     */
    public $missingImg = [];

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
        $this->actorFileService = $service->actorFileService;
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
     * @throws \Exception
     */
    public function process($actor)
    {
        try
        {
            $this->actorFileService->makeDirectory($this->nfnExportDir);

            $this->record = $this->actorRepoService->expedition
                ->skipCache()
                ->with(['project.group', 'subjects'])
                ->find($actor->pivot->expedition_id);

            $this->service->setWorkingDirectory("{$actor->id}-{$this->record->uuid}");
            $tempDir = "{$this->service->workingDir}/{$actor->id}-{$this->record->uuid}";
            $this->actorFileService->makeDirectory($tempDir);

            $fileAttributes = [
                'destination' => $tempDir,
                'extension'   => '.jpg',
                'width'       => $this->largeWidth,
                'height'      => $this->largeWidth
            ];

            $this->actorImageService->getImages($this->record->subjects, $fileAttributes);

            $this->buildCsvArray($this->record->subjects, $tempDir);

            if ($this->createCsv($tempDir))
            {
                $tarGzFiles = $this->actorFileService->compressDirectories($this->service->workingDir, $this->nfnExportDir);
                $this->actorRepoService->createDownloads($this->record->id, $actor->id, $tarGzFiles);
            }

            $this->actorFileService->filesystem->deleteDirectory($this->service->workingDir);

            $this->sendReport();

            $actor->pivot->queued = 0;
            ++$actor->pivot->state;
            $actor->pivot->save();
        }
        catch (FileNotFoundException $e)
        {
        }
        catch (RuntimeException $e)
        {
        }
        catch (Exception $e)
        {
            $actor->pivot->queued = 0;
            $actor->pivot->error = 1;
            $actor->pivot->save();

            $this->service->report->addError($e->getMessage());
            $this->service->report->reportSimpleError();
        }

    }

    /**
     * Build csvExport array for export.
     *
     * @param $subjects
     * @param $tempDir
     */
    public function buildCsvArray($subjects, $tempDir)
    {
        foreach ($subjects as $subject)
        {
            $file = $tempDir . '/' . $subject->_id . '.jpg';
            if ($this->actorFileService->checkFileExists($file))
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
