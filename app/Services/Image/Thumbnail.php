<?php namespace App\Services\Image;

use App\Exceptions\BiospexException;
use App\Exceptions\Handler;
use App\Exceptions\ThumbnailFromUrlException;
use App\Services\File\FileService;
use GuzzleHttp\Client;
use RuntimeException;

class Thumbnail extends ImagickService
{

    /**
     * @var FileService
     */
    private $fileService;
    /**
     * @var Handler
     */
    private $handler;

    /**
     * Thumbnail constructor.
     * @param FileService $fileService
     * @param Handler $handler
     */
    public function __construct(FileService $fileService, Handler $handler)
    {
        $this->fileService = $fileService;
        $this->handler = $handler;

        $this->defaultThumbImg = config('config.images.thumbDefaultImg');
        $this->tnWidth = config('config.images.thumbWidth');
        $this->tnHeight = config('config.images.thumbHeight');
        $this->thumbDirectory = config('config.images.thumbOutputDir') . '/' . $this->tnWidth . '_' . $this->tnHeight;
    }



    /**
     * Return thumbnail or create if not exists.
     *
     * @param $url
     * @return string
     *
     */
    public function getThumbnail($url)
    {
        $thumbName = md5($url) . '.jpg';
        $thumbFile = $this->thumbDirectory . '/' . $thumbName;

        try
        {
            if ( ! $this->fileService->filesystem->isFile($thumbFile))
            {
                $this->createThumbnail($url);
            }
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);
            return $this->getFile($this->defaultThumbImg);
        }

        return $this->getFile($thumbFile);

    }

    /**
     * Create thumbnail.
     *
     * @param $url
     * @throws ThumbnailFromUrlException
     */
    protected function createThumbnail($url)
    {
        $image = $this->thumbFromUrl($url);
        $this->createImagickObject();
        $this->readImagickFromBlob($image);
        $this->setDestinationImageWidth($this->tnWidth);
        $this->setDestinationImageHeight($this->tnHeight);
        $this->generateAndSaveImage(md5($url), $this->thumbDirectory);
        $this->clearImagickObject();
    }

    /**
     * Get image from url source.
     *
     * @param $url
     * @return string
     * @throws ThumbnailFromUrlException
     */
    protected function thumbFromUrl($url)
    {
        try {
            $client = new Client();
            $response = $client->get($url);

            return $response->getBody()->getContents();
        }
        catch(RuntimeException $e)
        {
            throw new ThumbnailFromUrlException($e->getMessage());
        }
    }

    /**
     * Get thumbnail file or default file.
     *
     * @param $thumbFile
     * @return string
     */
    protected function getFile($thumbFile)
    {
        if ($this->fileService->filesystem->isFile($thumbFile))
        {
            return $this->fileService->filesystem->get($thumbFile);
        }

        return $this->fileService->filesystem->get($this->defaultThumbImg);
    }
}
