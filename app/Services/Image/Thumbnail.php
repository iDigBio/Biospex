<?php
/**
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

namespace App\Services\Image;

use App\Services\File\FileService;
use Exception;
use GuzzleHttp\Client;
use Storage;

class Thumbnail extends ImagickService
{
    /**
     * @var FileService
     */
    public $fileService;

    /**
     * @var mixed
     */
    public $defaultThumbImg;

    /**
     * @var mixed
     */
    public $tnWidth;

    /**
     * @var mixed
     */
    public $tnHeight;

    /**
     * @var string
     */
    public $thumbDirectory;

    /**
     * Thumbnail constructor.
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;

        $this->defaultThumbImg = Storage::disk('public')->path(config('config.thumbDefaultImg'));
        $this->tnWidth = config('config.thumbWidth');
        $this->tnHeight = config('config.thumbHeight');
        $this->thumbDirectory = Storage::disk('public')->path(config('config.thumbOutputDir').'/'.$this->tnWidth.'_'.$this->tnHeight);
    }

    /**
     * Return thumbnail or create if not exists.
     *
     * @param $url
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getThumbnail($url)
    {
        $thumbName = md5($url).'.jpg';
        $thumbFile = $this->thumbDirectory.'/'.$thumbName;

        try {
            if (! $this->fileService->filesystem->isFile($thumbFile)) {
                $image = $this->thumbFromUrl($url);
                $this->createThumbnail($url, $image);
            }
        } catch (Exception $e) {
            return $this->getFile($this->defaultThumbImg);
        }

        return $this->getFile($thumbFile);
    }

    /**
     * Create thumbnail.
     *
     * @param $url
     * @param $image
     * @throws \Exception
     */
    protected function createThumbnail($url, $image)
    {
        $this->createImagickObject();
        $this->readImagickFromBlob($image);
        $this->setDestinationImageWidth($this->tnWidth);
        $this->setDestinationImageHeight($this->tnHeight);
        $this->resizeImage();
        $this->writeImagickImageToFile($this->thumbDirectory.'/'.md5($url).'.jpg');
    }

    /**
     * Get image from url source.
     *
     * @param $url
     * @return string
     */
    protected function thumbFromUrl($url)
    {
        $client = new Client();
        $response = $client->get($url);

        return $response->getBody()->getContents();
    }

    /**
     * Get thumbnail file or default file.
     *
     * @param $thumbFile
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getFile($thumbFile)
    {
        if ($this->fileService->filesystem->isFile($thumbFile)) {
            return $this->fileService->filesystem->get($thumbFile);
        }

        return $this->fileService->filesystem->get($this->defaultThumbImg);
    }
}
