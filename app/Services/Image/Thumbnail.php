<?php namespace App\Services\Image;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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
     * Return thumbnail or create if not exists.
     *
     * @param $url
     * @return null|string
     */
    public function getThumbnail($url)
    {
        $image = null;

        try
        {
            $this->setVars();
            $this->setOutPutFile($url);

            if ($this->filesystem->isFile($this->outputFileSm))
            {
                $this->setImageInfoFromFile($this->outputFileSm);

                $image = $this->filesystem->get($this->outputFileSm);
            }
            else
            {
                $image = $this->thumbFromUrl($url);
            }
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

        return $image;
    }

    /**
     * Set variables.
     *
     * @throws RuntimeException
     */
    public function setVars()
    {
        // We can read the output path from our configuration file.
        $this->defaultImg = $this->config->get('config.images.thumbDefaultImg');
        $this->tnWidth = $this->config->get('config.images.thumbWidth');
        $this->tnHeight = $this->config->get('config.images.thumbHeight');
        $this->outputDir = $this->config->get('config.images.thumbOutputDir') . '/' . $this->tnWidth . '_' . $this->tnHeight;

        if ( ! $this->filesystem->isDirectory($this->outputDir) && ! $this->filesystem->makeDirectory($this->outputDir, 0775, true))
        {
            throw new RuntimeException(trans('emails.error_create_dir', ['directory' => $this->outputDir]));
        }
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

    /**
     * Resize on the fly.
     *
     * @param $url
     * @return null|string
     */
    public function thumbFromUrl($url)
    {
        $client = new Client();
        $image = null;

        $response = $client->get($url);
        $this->saveThumbnail($response->getBody()->getContents());

        if ($this->filesystem->isFile($this->outputFileSm))
        {
            $this->setImageInfoFromFile($this->outputFileSm);
            $image = $this->filesystem->get($this->outputFileSm);
        }
        else
        {
            $this->setImageInfoFromFile($this->defaultImg);
            $image = $this->filesystem->get($this->defaultImg);
        }

        return $image;
    }


    /**
     * Save thumbnail.
     *
     * @param $image
     * @throws RuntimeException
     */
    public function saveThumbnail($image)
    {
        if ( ! $this->filesystem->put($this->outputFileLg, $image))
        {
            throw new RuntimeException(trans('emails.error_save_file', ['directory' => $this->outputFileLg]));
        }

        $this->imagickFile($this->outputFileLg);
        $this->imagickScale($this->outputFileSm, $this->tnWidth, 0);
        $this->filesystem->delete($this->outputFileLg);
        $this->imagickClear();
    }

}
