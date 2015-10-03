<?php namespace App\Services\Image;

use App\Services\Curl\Curl;
use App\Services\Curl\Request;

class Thumbnail extends Image
{
    /**
     * Output file path.
     * @var
     */
    protected $outputFile;

    /**
     * Default image.
     *
     * @var mixed
     */
    protected $defaultImg;

    /**
     * Output directory
     *
     * @var string
     */
    protected $outputDir;

    /**
     * Set variables.
     */
    public function setVars()
    {
        // We can read the output path from our configuration file.
        $this->defaultImg = \Config::get('config.images.thumb_default_img');
        $this->width = \Config::get('config.images.thumb_width');
        $this->height = \Config::get('config.images.thumb_height');
        $this->outputDir = \Config::get('config.images.thumb_output_dir') . '/' . $this->width . '_' . $this->height;
    }

    /**
     * Resize on the fly.
     *
     * @param $url
     * @return string
     */
    public function thumbFromUrl($url)
    {
        $this->setOutPutFile($url);
        $this->createDir($this->outputDir);

        if (File::isFile($this->outputFileSm)) {
            return $this->outputFileSm;
        }

        try {
            $rc = new Curl([$this, "saveThumbnail"]);
            $rc->options = [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLINFO_HEADER_OUT => 1];
            $rc->get($url);
            $rc->execute();
        } catch (Exception $e) {
            \Log::critical($e->getMessage());
        }

        return $this->outputFileSm;
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

        if (! $file = $this->thumbFromUrl($url)) {
            $file = $this->defaultImg;
        }

        return \File::get($file);
    }

    /**
     * Save thumb file.
     *
     * @param $image
     * @param $info
     */
    public function saveThumbnail($image, $info)
    {
        $this->saveFile($this->outputFileLg, $image);
        $this->imageMagick($this->outputFileLg);
        $this->resize($this->outputFileSm, $this->width, 0);
        $this->deleteImage($this->outputFileLg);

        return;
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
