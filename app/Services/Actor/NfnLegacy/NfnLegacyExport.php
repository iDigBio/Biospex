<?php

namespace App\Services\Actor\NfnLegacy;

use App\Notifications\NfnExportComplete;
use App\Notifications\NfnExportError;

ini_set('memory_limit', '1024M');

class NfnLegacyExport
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
     * @var \App\Services\File\FileService
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
     */
    public function __construct()
    {

        $this->nfnExportDir = config('config.nfn_export_dir');
        $this->largeWidth = config('config.images.nfnLrgWidth');
        $this->smallWidth = config('config.images.nfnSmWidth');
    }

    /**
     * Process current state
     *
     * @param $actor
     */
    public function process($actor)
    {
        try
        {
            $this->fileService->makeDirectory($this->nfnExportDir);

            $this->record = $this->actorRepoService->expeditionContract
                ->findWith($actor->pivot->expedition_id, ['project.group.owner', 'subjects']);

            $this->service->setWorkingDirectory("{$actor->id}-{$this->record->uuid}");

            $fileAttributes = [
                [
                    'destination' => $this->service->workingDir,
                    'extension'   => 'large.jpg',
                    'width'       => $this->largeWidth,
                    'height'      => $this->largeWidth,
                ],
                [
                    'destination' => $this->service->workingDir,
                    'extension'   => 'small.jpg',
                    'width'       => $this->smallWidth,
                    'height'      => $this->smallWidth,
                ]
            ];

            $this->actorImageService->getImages($this->record->subjects, $fileAttributes, $actor);

            $this->splitDirectories($this->record->subjects, "{$actor->id}-{$this->record->uuid}");

            $this->buildDetails();

            $tarGzFiles = $this->fileService->compressDirectories($this->service->workingDir, $this->nfnExportDir);

            $this->actorRepoService->createDownloads($this->record->id, $actor->id, $tarGzFiles);

            $this->fileService->filesystem->deleteDirectory($this->service->workingDir);

            $this->sendNotify();

            $actor->pivot->completed = 1;
            $actor->pivot->queued = 0;
            $actor->pivot->state = 1;
            $actor->pivot->save();
        }
        catch (\Exception $e)
        {
            $actor->pivot->queued = 0;
            $actor->pivot->error = 1;
            $actor->pivot->save();

            $message = trans('errors.nfn_export_error', [
                'title'   => $this->record->title,
                'id'      => $this->record->id,
                'message' => $e->getMessage()
            ]);

            $this->record->project->group->owner->notify(new NfnExportError($message));
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

            if ( ! $this->fileService->checkFileExists($lrgFilePath))
            {
                continue;
            }

            $size += filesize($lrgFilePath);
            $size += filesize($smFilePath);

            $this->fileService->filesystem->copy($lrgFilePath, $this->splitDir . '/' . $subject->_id . '.jpg');
            $this->fileService->filesystem->move($lrgFilePath, $this->splitDir . '/large/' . $subject->_id . '.large.jpg');
            $this->fileService->filesystem->move($smFilePath, $this->splitDir . '/small/' . $subject->_id . '.small.jpg');

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
        $directories = $this->fileService->filesystem->directories($this->service->workingDir);

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

            $files = $this->fileService->filesystem->files($directory);

            $i = 0;
            foreach ($files as $file)
            {
                $data = [];

                $fileName = $this->setOriginalImageData($file, $data);
                $this->setLargeImageData($fileName, $directory, $data);
                $this->setSmallImageData($fileName, $directory, $data);

                $metadata['images'][] = $data;

                $this->fileService->filesystem->delete($file);

                $i++;
            }

            $metadata['total'] = $i * 2;

            $this->fileService->filesystem->put($directory . '/details.js', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return $directories;
    }

    /**
     * @param $file
     * @param $data
     * @return mixed
     */
    public function setOriginalImageData($file, &$data)
    {
        // Original Image info.
        $this->actorImageService->imageService->setSourceFromFile($file);

        $baseName = $this->actorImageService->imageService->getSourceBaseName();
        $fileName = $this->actorImageService->imageService->getSourceFileName();

        $data['identifier'] = $fileName;
        $data['original']['path'] = [$fileName, '.jpg'];

        $data['original']['name'] = $baseName;
        $data['original']['width'] = $this->actorImageService->imageService->getSourceWidth();
        $data['original']['height'] = $this->actorImageService->imageService->getSourceHeight();

        $this->actorImageService->imageService->clearImagickObject();

        return $fileName;
    }

    /**
     * @param $fileName
     * @param $directory
     * @param $data
     */
    public function setLargeImageData($fileName, $directory, &$data)
    {
        $this->actorImageService->imageService->setSourceFromFile("$directory/large/$fileName.large.jpg");

        $data['large']['name'] = "large/$fileName.large.jpg";
        $data['large']['width'] = $this->actorImageService->imageService->getSourceWidth();
        $data['large']['height'] = $this->actorImageService->imageService->getSourceHeight();

        $this->actorImageService->imageService->clearImagickObject();
    }

    /**
     * @param $fileName
     * @param $directory
     * @param $data
     */
    public function setSmallImageData($fileName, $directory, &$data)
    {
        $this->actorImageService->imageService->setSourceFromFile("$directory/small/$fileName.small.jpg");
        $data['small']['name'] = "small/$fileName.small.jpg";
        $data['small']['width'] = $this->actorImageService->imageService->getSourceWidth();
        $data['small']['height'] = $this->actorImageService->imageService->getSourceHeight();

        $this->actorImageService->imageService->clearImagickObject();
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
        $this->fileService->makeDirectory($this->splitDir . '/large');
        $this->fileService->makeDirectory($this->splitDir . '/small');
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

    /**
     * Send notification for process completing.
     * @throws \Exception
     */
    protected function sendNotify()
    {
        $message = [
            $this->record->title,
            trans('emails.expedition_export_complete_message', ['expedition' => $this->record->title])
        ];

        $csv = create_csv($this->actorImageService->getMissingImages());

        $this->record->project->group->owner->email->notify(new NfnExportComplete($message, $csv));
    }
}
