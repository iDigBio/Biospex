<?php namespace App\Services\Image;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Thumbnail extends Image
{

    /**
     * Output file path.
     *
     * @var
     */
    protected $outputFile;

    /**
     * @var
     */
    protected $outputFileSm;

    /**
     * @var
     */
    protected $outputFileLg;

    /**
     * Output directory
     *
     * @var string
     */
    protected $outputDir;

    /**
     * Default image.
     *
     * @var mixed
     */
    private $defaultImg;

    /**
     * Thumbnail width.
     *
     * @var
     */
    private $tnWidth;

    /**
     * Thumbnail height.
     *
     * @var
     */
    private $tnHeight;

    /**
     * Set variables.
     */
    public function setVars()
    {
        // We can read the output path from our configuration file.
        $this->defaultImg = $this->config->get('config.images.thumbDefaultImg');
        $this->tnWidth = $this->config->get('config.images.thumbWidth');
        $this->tnHeight = $this->config->get('config.images.thumbHeight');
        $this->outputDir = $this->config->get('config.images.thumbOutputDir') . '/' . $this->tnWidth . '_' . $this->tnHeight;
        $this->filesystem->createDir($this->outputDir);
    }

    /**
     * Resize on the fly.
     *
     * @param $url
     * @return string
     */
    public function thumbFromUrl($url)
    {
        $client = new Client();
        try
        {
            $response = $client->get($url);
            $this->saveThumbnail($response->getBody()->getContents());

            if ($this->filesystem->isFile($this->outputFileSm))
            {
                $this->setImageSizeInfoFromFile($this->outputFileSm);
                $image = $this->filesystem->get($this->outputFileSm);
            }
            else
            {
                $this->setImageSizeInfoFromFile($this->defaultImg);
                $image = $this->filesystem->get($this->defaultImg);
            }

            return $image;
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }
    }

    /**
     * Return thumbnail or create if not exists.
     *
     * @param $url
     * @return string
     */
    public function getThumbnail($url)
    {
        $this->setVars();
        $this->setOutPutFile($url);

        if ($this->filesystem->isFile($this->outputFileSm))
        {
            $this->setImageSizeInfoFromFile($this->outputFileSm);

            return $this->filesystem->get($this->outputFileSm);
        }

        return $this->thumbFromUrl($url);

    }

    /**
     * Save thumb file.
     * @param $image
     */
    public function saveThumbnail($image)
    {
        $this->filesystem->saveFile($this->outputFileLg, $image);
        $this->imagickFile($this->outputFileLg);
        $this->imagickScale($this->outputFileSm, $this->tnWidth, 0);
        $this->filesystem->deleteFile($this->outputFileLg);
        $this->imagickDestroy();
    }

    /**
     * Set output file path.
     *
     * @param $url
     * @return string
     */
    public function setOutPutFile($url)
    {
        $filenameLg = md5($url) . '.jpg';
        $filenameSm = md5($url) . '.small.jpg';
        $this->outputFileLg = $this->outputDir . '/' . $filenameLg;
        $this->outputFileSm = $this->outputDir . '/' . $filenameSm;
    }

}
