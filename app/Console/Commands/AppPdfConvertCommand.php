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

namespace App\Console\Commands;

use App\Services\Csv\Csv;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class UpdateQueries
 *
 * @package App\Console\Commands
 */
class AppPdfConvertCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'convert:pdf';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var null
     */
    protected $imageDir = null;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * UpdateQueries constructor.
     */
    public function __construct(Csv $csv)
    {
        parent::__construct();
        $this->csv = $csv;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        //$this->convertPdf();
        //echo "complete" . PHP_EOL;
        //$this->renameFiles(); 5:45  1.046    1256 takes 20 minutes, 30 hours for 100K images
        //$this->renameCsv('fossils/mammal/images.csv','fossils/mammal/imagesNew.csv');
        //$this->renameCsv('fossils/bird/images.csv','fossils/bird/imagesNew.csv');
    }

    public function convertPdf()
    {
        $pdfPath = \Storage::path(config('config.aws_s3_scratch_dir').'/osteology');
        echo $pdfPath . PHP_EOL;
        collect(\File::files($pdfPath))->each(function($file){
            $this->writeFileToImage($file);
            echo 'writing image' . PHP_EOL;
            $this->combineImages($file);
            \File::deleteDirectory($this->imageDir);
            $this->imageDir = null;
        });
    }

    private function writeFileToImage($file)
    {
        $this->imageDir = \Storage::path(config('config.aws_s3_scratch_dir') . '/'.rand(10,12));
        \File::makeDirectory($this->imageDir);

        $im = new \Imagick();
        $im->setResourceLimit(6, 1);
        $im->setResolution(300,300);
        $im->readImage($file->getPathname());
        $im->setBackgroundColor('white');
        $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE );
        $im->setImageFormat('jpg');

        $im->writeImages($this->imageDir . '/' . $file->getBasename('.pdf') . '.jpg', false);
        $im->clear();
    }

    private function combineImages($file)
    {
        $name = $file->getBasename('.pdf') . '.jpg';

        $im = new \Imagick();
        $files = collect(\File::files($this->imageDir));
        $files->each(function ($file) use (&$im) {
            $im->setResolution(300,300);
            $im->readImage($file->getPathName());
        });

        $im->resetIterator();
        $combined = $im->appendImages(true);
        $combined->setImageFormat('jpg');
        $combined->writeImage( \Storage::path(config('config.aws_s3_scratch_dir') . '/combined/' . $name));
        $im->clear();
        $combined->clear();
    }

    public function renameFiles()
    {
        if (! \Storage::disk('public')->exists('tmpimage')) {
            \Storage::disk('public')->makeDirectory('tmpimage');
        }

        // trim(preg_replace("/[^ \w-]/", "", $data['county']));
        $publicDir = \Storage::disk('public')->path('original');
        $newDir = \Storage::disk('public')->path('tmpimage');

        $files = \File::files($publicDir);
        collect($files)->each(function ($file) use ($newDir) {
            $fileName = trim(preg_replace("/[^\w-]/", "", $file->getBasename('.jpg')));
            \File::copy($file->getPathname(), $newDir . '/' . $fileName . '.jpg');
        });
    }

    public function renameCsv(string $oldPath, string $newPath)
    {
        $newFile = \Storage::path($newPath);
        $this->csv->writerCreateFromPath($newFile);

        $file = \Storage::path($oldPath);
        $this->csv->readerCreateFromPath($file);
        $this->csv->setHeaderOffset();

        $this->csv->insertOne($this->csv->getHeader());

        $rows = $this->csv->getRecords();
        foreach ($rows as $offset => $row) {
            $subStr = substr($row['title'], 0, strrpos($row['title'], '.'));
            $title = trim(preg_replace("/[^\w-]/", "", $subStr));
            $accessUri = "https://biospex.org/tmpimage/" . $title . '.jpg';

            $row['accessURI'] = $accessUri;
            $this->csv->insertOne($row);
        }
    }
}

