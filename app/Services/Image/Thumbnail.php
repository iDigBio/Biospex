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
use Illuminate\Support\Facades\File;
use Storage;

/**
 * Class Thumbnail
 *
 * @package App\Services\Image
 */
class Thumbnail
{

    /**
     * @var mixed
     */
    public mixed $defaultThumbImg;

    /**
     * @var string
     */
    public string $tnWidth;

    /**
     * @var string
     */
    public string $tnHeight;

    /**
     * @var string
     */
    public string $thumbDirectory;

    /**
     * @var string
     */
    public string $imageProcessFile;

    /**
     * Thumbnail constructor.
     */
    public function __construct()
    {
        $this->defaultThumbImg = Storage::disk('public')->path(config('config.thumb_default_img'));
        $this->tnWidth = config('config.thumb_width');
        $this->tnHeight = config('config.thumb_height');
        $this->thumbDirectory = Storage::disk('public')->path(config('config.thumb_output_dir').'/'.$this->tnWidth.'_'.$this->tnHeight);
        $this->imageProcessFile = config('config.image_process_file');
    }

    /**
     * Return thumbnail or create if not exists.
     *
     * @param $url
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getThumbnail($url): string
    {
        $thumbName = md5($url).'.jpg';
        $thumbFile = $this->thumbDirectory.'/'.$thumbName;

        try {
            if (! File::isFile($thumbFile)) {
                $this->processImage($url, $thumbName);
            }
        } catch (Exception $e) {
            return $this->getFile($this->defaultThumbImg);
        }

        return $this->getFile($thumbFile);
    }

    /**
     * Get image and create thumbnail.
     *
     * @param string $url
     * @param string $fileName
     * @return void
     * @throws \Exception
     */
    protected function processImage(string $url, string $fileName)
    {
        $output = null;
        $command = "node {$this->imageProcessFile} $fileName $url {$this->thumbDirectory} {$this->tnWidth} {$this->tnHeight}";
        \Log::alert($command);
        exec($command, $output);

        if (!$output[0]) {
            throw new Exception('Could not retrieve image for thumbnail.');
        }
    }

    /**
     * Get thumbnail file or default file.
     *
     * @param $thumbFile
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getFile($thumbFile): string
    {
        if (File::isFile($thumbFile)) {
            return File::get($thumbFile);
        }

        return File::get($this->defaultThumbImg);
    }
}
