<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Actor;

putenv('MAGICK_THREAD_LIMIT=1');

use App\Facades\ActorEventHelper;
use App\Models\Actor;
use App\Services\Model\ExportQueueFileService;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;
use App\Services\Image\ImagickService;
use App\Services\Requests\HttpRequest;
use Illuminate\Support\Collection;
use File;
use ImagickException;

/**
 * Class ActorImageService
 *
 * @package App\Services\Actor
 */
class ActorImageService
{
    /**
     * @var ImagickService
     */
    private $imagickService;

    /**
     * @var HttpRequest
     */
    private $httpRequest;

    /**
     * @var \App\Models\Actor
     */
    private $actor;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $files = null;

    /**
     * @var string
     */
    private $workingDirectory = null;

    /**
     * @var string
     */
    private $tmpDirectory = null;

    /**
     * @var \App\Services\Model\ExportQueueFileService
     */
    private $exportQueueFileService;

    /**
     * ActorImageService constructor.
     *
     * @param ImagickService $imagickService
     * @param HttpRequest $httpRequest
     * @param \App\Services\Model\ExportQueueFileService $exportQueueFileService
     * @internal param ImageService $imageService
     */
    public function __construct(
        ImagickService $imagickService,
        HttpRequest $httpRequest,
        ExportQueueFileService $exportQueueFileService
    ) {
        $this->imagickService = $imagickService;
        $this->httpRequest = $httpRequest;
        $this->exportQueueFileService = $exportQueueFileService;
    }

    /**
     * Set the actor being worked on for firing events.
     *
     * @param \App\Models\Actor $actor
     */
    public function setActor(Actor $actor)
    {
        $this->actor = $actor;
    }

    /**
     * Set files collection to process.
     *
     * @param \Illuminate\Support\Collection $files
     */
    public function setFiles(Collection $files)
    {
        $this->files = $files;
    }

    /**
     * Set directories.
     *
     * @param string $workingDirectory
     * @param string $tmpDirectory
     */
    public function setDirectories(string $workingDirectory, string $tmpDirectory)
    {
        $this->workingDirectory = $workingDirectory;
        $this->tmpDirectory = $tmpDirectory;
    }

    /**
     * Check necessary properties are set.
     *
     * @return bool
     */
    private function propertiesCheck()
    {
        return $this->actor === null || $this->workingDirectory === null || $this->tmpDirectory === null;
    }

    /**
     * Process images.
     *
     * @throws \Exception
     */
    public function getImages()
    {
        if ($this->propertiesCheck()) {
            throw new Exception(t('Missing needed properties for ActorImageService'));
        }

        $this->httpRequest->setHttpProvider();

        $requests = function () {
            foreach ($this->files as $index => $file) {
                $filePath = $this->workingDirectory.'/'.$file->subject_id.'.jpg';
                if ($this->checkPropertiesExist($file) || File::isFile($filePath)) {
                    ActorEventHelper::fireActorProcessedEvent($this->actor);

                    continue;
                }

                $uri = str_replace(' ', '%20', $file->url);

                yield $index => new Request('GET', $uri);
            }
        };

        $pool = new Pool($this->httpRequest->getHttpClient(), $requests(), [
            'concurrency' => 5,
            'fulfilled'   => function ($response, $index) {
                $this->saveImage($response, $index);
            },
            'rejected'    => function ($reason, $index) {
                ActorEventHelper::fireActorProcessedEvent($this->actor);
                $this->setFileErrorMessage($this->files[$index], 'Could not retrieve image from uri.');
            },
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    /**
     * Save image to image path.
     *
     * @param $response
     * @param $index
     * @throws \Exception
     */
    private function saveImage($response, $index)
    {
        $image = $response->getBody()->getContents();
        $response->getBody()->close();

        if ($image === '' || $response->getStatusCode() !== 200) {
            $this->setFileErrorMessage($this->files[$index], 'Image string empty or status code not 200.');
            ActorEventHelper::fireActorProcessedEvent($this->actor);

            return;
        }

        $this->processBlobImage($image, $index);
        ActorEventHelper::fireActorProcessedEvent($this->actor);
    }

    /**
     * Process Image.
     *
     * @param $image
     * @param $index
     * @throws \Exception
     */
    public function processBlobImage($image, $index)
    {
        try {
            $this->imagickService->createImagickObject();
            $this->imagickService->readImagickFromBlob($image);
            $this->imagickService->setImageFormat();
            $this->imagickService->stripImage();
            $this->writeImagickFile($this->workingDirectory, $this->files[$index]->subject_id);
            $this->imagickService->clearImagickObject();
        } catch (ImagickException $e) {
            $this->setFileErrorMessage($this->files[$index], $e->getMessage());
            $this->imagickService->clearImagickObject();
        }
    }

    /**
     * @param $file
     * @param $fileName
     * @throws \Exception
     */
    public function processFileImage($file, $fileName)
    {
        try {
            $this->imagickService->createImagickObject();
            $this->imagickService->setOption('jpeg:size', '1540x1540');
            $this->imagickService->readImageFromPath($file);
            $this->imagickService->setOption('jpeg:extent', '600kb');
            $this->writeImagickFile($this->tmpDirectory, $fileName);
            $this->imagickService->clearImagickObject();
        } catch (ImagickException $e) {
            $this->setFileErrorMessage($fileName, $e->getMessage());
            $this->imagickService->clearImagickObject();
        }
    }

    /**
     * Add missing image information to array.
     *
     * @param $file
     * @param string $message
     */
    private function setFileErrorMessage($file, string $message)
    {
        if ($file instanceof \App\Models\ExportQueueFile) {
            $file->error = 1;
            $file->error_message = $message;
            $file->save();

            return;
        }

        $result = $this->exportQueueFileService->findBy('subject_id', $file);
        $result->error = 1;
        $result->error_message = $message;
        $result->save();
    }

    /**
     * Check properties to see if image needs to be retrieved.
     *
     * @param \App\Models\ExportQueueFile $file
     * @return bool
     */
    private function checkPropertiesExist(\App\Models\ExportQueueFile $file)
    {
        if (empty($file->url)) {
            $this->setFileErrorMessage($file, 'Image missing url value');

            return true;
        }

        return false;
    }

    /**
     * Write imagick to file.
     *
     * @param string $dir
     * @param $fileName
     * @throws \Exception
     */
    public function writeImagickFile($dir, $fileName)
    {
        $destination = $dir.'/'.$fileName.'.jpg';
        if (! $this->imagickService->writeImagickImageToFile($destination)) {
            throw new ImagickException('Could not write image '.$destination);
        }
    }
}