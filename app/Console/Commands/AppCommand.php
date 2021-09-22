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

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
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
     * AppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $pdfPath = \Storage::path(config('config.scratch_dir'));
        $imgPath = \Storage::path(config('config.scratch_dir') . '/images');
        $filename = 'ZR9_41.pdf';

        $im = new \Imagick();
        $im->setResourceLimit(6, 1);
        $im->setResolution(300,300);
        $im->readImage($pdfPath . '/Z80_1_64.pdf');
        $im->setBackgroundColor('white');
        $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE );
        $im->setImageFormat('jpg');

        $im->writeImages($imgPath . '/Z80_1_64.jpg', false);
        $im->clear();

        $im = new \Imagick();
        $files = collect(\File::files($imgPath));
        $files->each(function ($file) use (&$im) {
            $im->setResolution(300,300);
            $im->readImage($file->getPathName());
        });

        $im->resetIterator();
        $combined = $im->appendImages(true);
        $combined->setImageFormat('jpg');
        $combined->writeImage($imgPath . '/ZR9_41.jpg');
        $im->clear();
        $combined->clear();

    }
}