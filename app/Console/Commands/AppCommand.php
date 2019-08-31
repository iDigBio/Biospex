<?php

namespace App\Console\Commands;

use App\Services\Requests\HttpRequest;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Services\Requests\HttpRequest
     */
    private $request;

    /**
     * AppCommand constructor.
     */
    public function __construct(HttpRequest $request)
    {
        parent::__construct();
        $this->request = $request;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $projectId = 13;

        //$this->createChartImage($projectId);

        $this->trimImage($projectId);
    }

    public function createChartImage($projectId)
    {
        $dir = 'charts';
        $projectFolderPath = $dir . '/' . $projectId;
        $projectFilePath = $dir . '/' . $projectId . '.png';
        $amChartFilePath = $dir . '/' . $projectId . '/amCharts.png';

        \Storage::makeDirectory($projectFolderPath);
        exec("node test2-download $projectId", $output);
        if ($output[0] == "true") {
            if (\Storage::exists($projectFilePath)) {
                \Storage::delete($projectFilePath);
            }
            \Storage::move($amChartFilePath, $projectFilePath);
            \Storage::deleteDirectory($dir . '/' . $projectId);
            exit;
        }
        // error output
        exit(implode(PHP_EOL, $output));

    }

    public function trimImage($projectId)
    {
        $projectFilePath = \Storage::path('charts/'.$projectId.'.png');

        $myim = imagecreatefrompng($projectFilePath);
        $myim = $this->imageTrim($myim, imagecolorallocate($myim, 0xFF, 0xFF, 0xFF), 10);
        //$myim now holds the new image which has had white trimmed and a padding of 10px around the image added.
        imagepng($myim, $projectFilePath); // Return the newly cropped image
    }

    // Trims an image then optionally adds padding around it.
    // $im  = Image link resource
    // $bgcol  = The background color to trim from the image (using imagecolorallocate in gd)
    // $pad = Amount of padding to add to the trimmed image
    //        (acts similar to the "padding" CSS property: "top [right [bottom [left]]]")
    public function imageTrim($im, $bgcol, $pad = null)
    {

        // Calculate padding for each side.
        if (isset($pad)) {
            $pada = explode(' ', $pad);
            if (isset($pada[3])) {
                $p = [(int) $pada[0], (int) $pada[1], (int) $pada[2], (int) $pada[3]];
            } else {
                if (isset($pada[2])) {
                    $p = [(int) $pada[0], (int) $pada[1], (int) $pada[2], (int) $pada[1]];
                } else {
                    if (isset($pada[1])) {
                        $p = [(int) $pada[0], (int) $pada[1], (int) $pada[0], (int) $pada[1]];
                    } else {
                        $p = array_fill(0, 4, (int) $pada[0]);
                    }
                }
            }
        } else {
            $p = array_fill(0, 4, 0);
        }

        // Get the width and height of the image.
        $imw = imagesx($im);
        $imh = imagesy($im);

        // Set the X variables.
        $xmin = $imw;
        $xmax = 0;

        // find the endges.
        for ($iy = 0; $iy < $imh; $iy++) {
            $first = true;
            for ($ix = 0; $ix < $imw; $ix++) {
                $ndx = imagecolorat($im, $ix, $iy);
                if ($ndx != $bgcol) {
                    if ($xmin > $ix) {
                        $xmin = $ix;
                    }
                    if ($xmax < $ix) {
                        $xmax = $ix;
                    }
                    if (! isset($ymin)) {
                        $ymin = $iy;
                    }
                    $ymax = $iy;
                    if ($first) {
                        $ix = $xmax;
                        $first = false;
                    }
                }
            }
        }

        // The new width and height of the image. (not including padding)
        $imw = 1 + $xmax - $xmin; // Image width in pixels
        $imh = 1 + $ymax - $ymin; // Image height in pixels

        // Make another image to place the trimmed version in.
        $im2 = imagecreatetruecolor($imw + $p[1] + $p[3], $imh + $p[0] + $p[2]);

        // Make the background of the new image the same as the background of the old one.
        $bgcol2 = imagecolorallocate($im2, ($bgcol >> 16) & 0xFF, ($bgcol >> 8) & 0xFF, $bgcol & 0xFF);
        imagefill($im2, 0, 0, $bgcol2);

        // Copy it over to the new image.
        imagecopy($im2, $im, $p[3], $p[0], $xmin, $ymin, $imw, $imh);

        // To finish up, return the new image.
        return $im2;
    }
}