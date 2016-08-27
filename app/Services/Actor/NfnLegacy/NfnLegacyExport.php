<?php

namespace App\Services\Actor\NfnLegacy;

use App\Services\Actor\ActorInterface;
use App\Services\Actor\ActorService;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use RuntimeException;

ini_set('memory_limit', '1024M');

class NfnLegacyExport implements ActorInterface
{

    /**
     * @var
     */
    protected $record;

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

    /** Notes from Nature export directory */
    protected $nfnExportDir;

    /**
     * Directories built based on 1GB file sizes or less.
     *
     * @var
     */
    protected $splitDir;

    /**
     * Count of chunk directory.
     *
     * @var
     */
    protected $count = 0;

    /**
     * @var
     */
    public $lrgFilePath;

    /**
     * @var
     */
    public $smFilePath;

    /**
     * @var mixed
     */
    private $largeWidth;

    /**
     * @var mixed
     */
    private $smallWidth;

    /**
     * NfnLegacyExport constructor.
     *
     * @param ActorService $service
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
        $this->largeWidth = $this->service->config->get('config.images.nfnLrgWidth');
        $this->smallWidth = $this->service->config->get('config.images.nfnSmWidth');
    }

    /**
     * Process current state
     *
     * @param $actor
     * @return mixed|void
     * @throws \RuntimeException|\Exception
     */
    public function process($actor)
    {
        try {
            $this->actorFileService->makeDirectory($this->nfnExportDir);

            $this->record = $this->actorRepoService->expedition
                ->skipCache()
                ->with(['project.group', 'subjects'])
                ->find($actor->pivot->expedition_id);

            $this->service->setWorkingDirectory("{$actor->id}-{$this->record->uuid}");

            $fileAttributes = [
                [
                    'destination' => $this->service->workingDir,
                    'extension' => '.large.jpg',
                    'width' => $this->largeWidth,
                    'height' => $this->largeWidth,
                ],
                [
                    'destination' => $this->service->workingDir,
                    'extension' => '.small.jpg',
                    'width' => $this->smallWidth,
                    'height' => $this->smallWidth,
                ]
            ];

            $this->actorImageService->getImages($this->record->subjects, $fileAttributes);

            $this->splitDirectories($this->record->subjects, "{$actor->id}-{$this->record->uuid}");

            $this->buildDetails();

            $tarGzFiles = $this->actorFileService->compressDirectories($this->service->workingDir, $this->nfnExportDir);

            $this->actorRepoService->createDownloads($this->record->id, $actor->id, $tarGzFiles);

            $this->actorFileService->filesystem->deleteDirectory($this->service->workingDir);

            $this->sendReport($this->record);

            $actor->pivot->completed = 1;
            $actor->pivot->queued = 0;
            ++$actor->pivot->state;
            $actor->pivot->save();
        }
        catch(FileNotFoundException $e) {}
        catch(RuntimeException $e) {}
        catch(Exception $e)
        {
            $actor->pivot->queued = 0;
            $actor->pivot->error = 1;
            $actor->pivot->save();

            $this->service->report->addError($e->getMessage() . ' : ' . $e->getFile() . ' : ' . $e->getLine());
            $this->service->report->reportSimpleError();
        }

    }

    /**
     * Split tmp directory into separate directories based on size.
     *
     * @param array $subjects
     * @param $folder
     */
    public function splitDirectories($subjects, $folder)
    {
        $size = 0;
        $this->setSplitDir($folder);
        $limit = $this->getDirectorySize();

        foreach ($subjects as $subject)
        {
            $lrgFilePath = $this->service->workingDir . '/' . $subject->_id . '.large.jpg';
            $smFilePath = $this->service->workingDir . '/' . $subject->_id . '.small.jpg';

            if ( ! $this->actorFileService->checkFileExists($lrgFilePath))
            {
                continue;
            }

            $size += filesize($lrgFilePath);
            $size += filesize($smFilePath);

            $this->actorFileService->filesystem->copy($lrgFilePath, $this->splitDir . '/' . $subject->_id . '.jpg');
            $this->actorFileService->filesystem->move($lrgFilePath, $this->splitDir . '/large/' . $subject->_id . '.large.jpg');
            $this->actorFileService->filesystem->move($smFilePath, $this->splitDir . '/small/' . $subject->_id . '.small.jpg');

            if ($size >= $limit)
            {
                $this->setSplitDir($folder);
                $size = 0;
            }

        }
    }

    /**
     * Build detail.js file.
     */
    public function buildDetails()
    {
        $directories = $this->actorFileService->filesystem->directories($this->service->workingDir);

        foreach ($directories as $directory)
        {
            $metadata = [];
            $metadata['sourceDir'] = $directory;
            $metadata['targetDir'] = $directory;
            $metadata['created_at'] = date('l jS F Y', time());
            $metadata['highResDir'] = $directory . '/large';
            $metadata['lowResDir'] = $directory . '/small';
            $metadata['highResWidth'] = $this->largeWidth;
            $metadata['lowResWidth'] = $this->smallWidth;
            $metadata['total'] = 0;
            $metadata['images'] = [];

            $files = $this->actorFileService->filesystem->files($directory);

            $i = 0;
            foreach ($files as $file)
            {
                $data = [];

                // Original Image info.
                $this->actorImageService->imageService->setSourceFromFile($file);

                $baseName = $this->actorImageService->imageService->getSourceBaseName();
                $fileName = $this->actorImageService->imageService->getSourceFileName();

                $data['identifier'] = $fileName;
                $data['original']['path'] = [$fileName, '.jpg'];

                $data['original']['name'] = $baseName;
                $data['original']['width'] = $this->actorImageService->imageService->getSourceWidth();
                $data['original']['height'] = $this->actorImageService->imageService->getSourceHeight();

                $this->actorImageService->imageService->destroySource();

                // Set array for large image.

                $this->actorImageService->imageService->setSourceFromFile("$directory/large/$fileName.large.jpg");
                $data['large']['name'] = "large/$fileName.large.jpg";
                $data['large']['width'] = $this->actorImageService->imageService->getSourceWidth();
                $data['large']['height'] = $this->actorImageService->imageService->getSourceHeight();

                $this->actorImageService->imageService->destroySource();

                // Set array for small image.
                $this->actorImageService->imageService->setSourceFromFile("$directory/small/$fileName.small.jpg");
                $data['small']['name'] = "small/$fileName.small.jpg";
                $data['small']['width'] = $this->actorImageService->imageService->getSourceWidth();
                $data['small']['height'] = $this->actorImageService->imageService->getSourceHeight();

                $this->actorImageService->imageService->destroySource();

                $metadata['images'][] = $data;

                $this->actorFileService->filesystem->delete($file);

                $i++;
            }

            $metadata['total'] = $i * 2;

            $this->actorFileService->filesystem->put($directory . '/details.js', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return $directories;
    }

    /**
     * Create split directories.
     *
     * @param $folder
     */
    public function setSplitDir($folder)
    {
        $count = ++$this->count;
        $this->splitDir = $this->service->workingDir . '/' . $folder . '-' . $count;
        $this->actorFileService->makeDirectory($this->splitDir . '/large');
        $this->actorFileService->makeDirectory($this->splitDir . '/small');
    }

    /**
     * Set directory sizes for download files.
     *
     * @return float
     */
    protected function getDirectorySize()
    {
        exec("du -b -s {$this->service->workingDir}", $op);
        list($size) = preg_split('/\s+/', $op[0]);

        $gb = 1073741824;

        return ($size < $gb) ? $size : ceil($size / ceil(number_format($size / $gb, 2)));
    }

    protected function buildImageData(&$data)
    {

    }

    /**
     * Send report for process completing.
     *
     * @param $record
     */
    protected function sendReport($record)
    {
        $vars = [
            'title' => $record->title,
            'message' => trans('emails.expedition_export_complete_message', ['expedition' => $record->title]),
            'groupId' => $record->project->group->id,
            'attachmentName' => trans('emails.missing_images_attachment_name', ['recordId' => $record->id])
        ];

        $this->service->processComplete($vars, $this->actorImageService->getMissingImages());
    }
}
