<?php namespace App\Services\Image;

use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use RuntimeException;

class Thumbnail extends ImageService
{

    /**
     * Return thumbnail or create if not exists.
     *
     * @param $url
     * @return string
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

            return $this->getFile($thumbFile);

        }
        catch (RuntimeException $e)
        {
            $this->report->addError($e->getMessage());
            $this->report->reportSimpleError();
        }
        catch (FileNotFoundException $e)
        {
            $this->report->addError($e->getMessage());
            $this->report->reportSimpleError();
        }
    }

    /**
     * Create thumbnail.
     *
     * @param $url
     * @throws RuntimeException
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
     * @throws RuntimeException
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
     * @throws FileNotFoundException
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
