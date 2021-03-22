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

use App\Models\ExportQueueFile;
use App\Services\Image\ImagickService;
use File;
use Illuminate\Support\LazyCollection;
use ImagickException;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;

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
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var array
     */
    private $rejected = [];

    /**
     * ActorImageService constructor.
     *
     * @param ImagickService $imagickService
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(
        ImagickService $imagickService,
        Client $client

    ) {
        $this->imagickService = $imagickService;
        $this->client = $client;
    }

    /**
     * Return rejected files.
     *
     * @return array
     */
    public function getRejected(): array
    {
        return $this->rejected;
    }

    /**
     * Retrieve images.
     *
     * @param \Illuminate\Support\LazyCollection $files
     * @param $wrkDir
     */
    public function retrieveImages(LazyCollection $files, $wrkDir)
    {
        $checkedFiles = $this->checkFileProperties($files, $wrkDir);

        $promises = (function () use ($checkedFiles, $wrkDir) {
            foreach ($checkedFiles as $file) {
                $uri = str_replace(' ', '%20', $file->url);
                yield $file->subject_id => $this->client->getAsync($uri);;
            }
        })();

        $eachPromise = new EachPromise($promises, [
            // how many concurrency we are use
            'concurrency' => 5,
            'fulfilled'   => function (Response $response, $index) use ($wrkDir) {
                if ($response->getStatusCode() == 200) {
                    $image = $response->getBody();
                    if ($this->checkImageString($image)) {
                        $this->saveImage($image, $index, $wrkDir);

                        return;
                    }

                    $this->rejected[$index] = 'Image format not recognized';
                }
            },
            'rejected'    => function ($reason, $index) {
                $this->rejected[$index] = $reason;
            },
        ]);

        $eachPromise->promise()->wait();
    }

    /**
     * Check response is image.
     *
     * @param $data
     * @return bool
     */
    public function checkImageString($data): bool
    {
        $im = @imagecreatefromstring($data);

        return $im !== false;
    }

    /**
     * Check file properties and if file exists already.
     *
     * @param \Illuminate\Support\LazyCollection $files
     * @param string $wrkDir
     * @return \Illuminate\Support\LazyCollection
     */
    private function checkFileProperties(LazyCollection $files, string $wrkDir): LazyCollection
    {
        return $files->filter(function($file){
            return $this->checkUrlExist($file);
        })->reject(function($file) use ($wrkDir) {
            return $this->checkFile($wrkDir.'/'.$file->subject_id.'.jpg', $file->subject_id);
        });
    }

    /**
     * Check properties to see if image needs to be retrieved.
     *
     * @param \App\Models\ExportQueueFile $file
     * @return bool
     */
    public function checkUrlExist(ExportQueueFile $file): bool
    {
        if($file->url !== null) {
            return true;
        }

        $this->rejected[$file->subject_id] = 'Missing image url';

        return false;
    }

    /**
     * Check if file exists and is image.
     *
     * @param string $filePath
     * @param string $subjectId
     * @param bool $addRejected
     * @return bool
     */
    public function checkFile(string $filePath, string $subjectId, bool $addRejected = false): bool
    {
        if (!File::exists($filePath)) {
            if ($addRejected) {
                $this->rejected[$subjectId] = 'Missing image on disk';
            }
            return false;
        }

        if (File::exists($filePath) && false === exif_imagetype($filePath)) {
            if ($addRejected) {
                $this->rejected[$subjectId] = 'Image file corrupted';
            }
            return false;
        }

        return true;
    }

    /**
     * Save image to image path.
     *
     * @param string $image
     * @param string $subjectId
     * @param string $wrkDir
     */
    public function saveImage(string $image, string $subjectId, string $wrkDir)
    {
        try {
            $this->imagickService->createImagickObject();
            $this->imagickService->readImagickFromBlob($image);
            $this->imagickService->setImageFormat();
            $this->imagickService->stripImage();
            $this->writeImagickFile($wrkDir, $subjectId);
        } catch (\Exception $exception) {
            $this->rejected[$subjectId] = 'Could not create Imagick image';
        }
    }

    /**
     * Process image to size.
     *
     * @param string $filePath
     * @param string $tmpDir
     * @param string $fileName
     */
    public function processFileImage(string $filePath, string $tmpDir, string $fileName)
    {
        try {
            $this->imagickService->createImagickObject();
            $this->imagickService->setOption('jpeg:size', '1540x1540');
            $this->imagickService->readImageFromPath($filePath);
            $this->imagickService->setOption('jpeg:extent', '600kb');
            $this->writeImagickFile($tmpDir, $fileName);
            $this->imagickService->clearImagickObject();
        } catch (ImagickException $e) {
            $this->rejected[$fileName] = $e->getMessage();
            $this->imagickService->clearImagickObject();
        }
    }

    /**
     * Write imagick to file.
     *
     * @param string $dir
     * @param $fileName
     */
    public function writeImagickFile(string $dir, string $fileName)
    {
        $destination = $dir.'/'.$fileName.'.jpg';
        if (! $this->imagickService->writeImagickImageToFile($destination)) {
            $this->imagickService->clearImagickObject();
            $this->rejected[$fileName] = 'Could not write image to disk';
        }

        $this->imagickService->clearImagickObject();
    }
}