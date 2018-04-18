<?php

namespace App\Services\Image;

use App\Services\File\FileService;
use GuzzleHttp\Client;

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

        $this->defaultThumbImg = config('config.images.thumbDefaultImg');
        $this->tnWidth = config('config.images.thumbWidth');
        $this->tnHeight = config('config.images.thumbHeight');
        $this->thumbDirectory = config('config.images.thumbOutputDir').'/'.$this->tnWidth.'_'.$this->tnHeight;
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
        } catch (\Exception $e) {
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
