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
     * @return string|void
     *
     */
    public function getThumbnail($url)
    {
        $thumbName = md5($url) . '.small.jpg';
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
        $this->setSourceFromString($image);

        $fileAttributes = [
            'destination' => $this->thumbDir,
            'extension'   => '.small.jpg',
            'width'       => $this->tnWidth,
            'height'      => $this->tnHeight
        ];

        $this->generateAndSaveImage(md5($url), $fileAttributes);
        $this->destroySource();
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
