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

namespace App\Services\Image;

use Exception;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Filesystem\Filesystem as File;
use Throwable;

/**
 * Class Thumbnail
 */
class Thumbnail
{
    private mixed $defaultThumbImg;

    private string $tnWidth;

    private string $tnHeight;

    private string $thumbDirectory;

    private string $imageProcessFile;

    /**
     * Thumbnail constructor.
     */
    public function __construct(protected Storage $storage, protected File $file)
    {
        $this->setVariables();
    }

    /**
     * Set variables.
     */
    private function setVariables(): void
    {
        $this->setDefaultThumbnailImage();
        $this->setThumbnailWidthHeight();
        $this->setThumbnailDir();
        $this->setImageProcessFile();
    }

    /**
     * Set default thumbnail image.
     */
    private function setDefaultThumbnailImage(): void
    {
        $this->defaultThumbImg = $this->storage->disk('public')->path(config('config.thumb_default_img'));
    }

    /**
     * Set thumbnail width and height.
     */
    private function setThumbnailWidthHeight(): void
    {
        $this->tnWidth = config('config.thumb_width');
        $this->tnHeight = config('config.thumb_height');
    }

    /**
     * Set thumbnail directory.
     */
    private function setThumbnailDir(): void
    {

        $this->thumbDirectory = $this->storage->disk('public')
            ->path(config('config.thumb_output_dir').'/'.$this->tnWidth.'_'.$this->tnHeight);
    }

    /**
     * Set image process file.
     */
    private function setImageProcessFile(): void
    {
        $this->imageProcessFile = config('config.image_process_file'); //image-process.js
    }

    /**
     * Return thumbnail or create if not exists.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getThumbnail($url): string
    {
        $thumbName = md5($url).'.jpg';
        $thumbFilePath = $this->thumbDirectory.'/'.$thumbName;

        try {
            if (! $this->file->isFile($thumbFilePath)) {
                $this->processImage($url, $thumbFilePath);
            }
        } catch (Throwable $throwable) {

            return $this->getFile($this->defaultThumbImg);
        }

        return $this->getFile($thumbFilePath);
    }

    /**
     * Get image and create thumbnail.
     *
     * @throws \Exception
     */
    protected function processImage(string $url, string $filePath): void
    {
        $output = null;
        $command = "node {$this->imageProcessFile} $filePath $url";

        exec($command, $output);

        if (! $output[0]) {
            throw new Exception('Could not retrieve image for thumbnail.');
        }
    }

    /**
     * Get thumbnail file or default file.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getFile($thumbFile): string
    {
        if ($this->file->isFile($thumbFile)) {
            return $this->file->get($thumbFile);
        }

        return $this->file->get($this->defaultThumbImg);
    }
}
