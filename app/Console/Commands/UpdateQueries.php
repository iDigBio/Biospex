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

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class UpdateQueries
 *
 * @package App\Console\Commands
 */
class UpdateQueries extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    protected $imageDir = null;

    /**
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $pdfPath = \Storage::path(config('config.scratch_dir').'/osteology');
        collect(\File::files($pdfPath))->each(function($file){
            $this->writeFileToImage($file);
            $this->combineImages($file);
            \File::deleteDirectory($this->imageDir);
            $this->imageDir = null;
        });
    }

    private function writeFileToImage($file)
    {
        $this->imageDir = \Storage::path(config('config.scratch_dir') . '/'.rand(10,12));
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
        $combined->writeImage( \Storage::path(config('config.scratch_dir') . '/combined/' . $name));
        $im->clear();
        $combined->clear();
    }
}