<?php namespace App\Services\Image;

use App\Exceptions\BiospexException;
use App\Exceptions\ThumbnailFromUrlException;
use GuzzleHttp\Client;
use RuntimeException;

class Thumbnail extends ImageService
{

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
        $thumbFile = $this->thumbDir . '/' . $thumbName;

        try
        {
            if ( ! $this->filesystem->isFile($thumbFile))
            {
                $this->createThumbnail($url);
            }
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);
            return $this->getFile($this->defaultImg);
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
        $this->generateAndSaveImage(md5($url), $this->thumbDir);
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
        if ($this->filesystem->isFile($thumbFile))
        {
            return $this->filesystem->get($thumbFile);
        }

        return $this->filesystem->get($this->defaultImg);
    }
}
