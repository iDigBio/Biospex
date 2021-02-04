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
use Phar;

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
    public function __construct(
    ) {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $tmpDir = "/home/vagrant/sites/biospexProd/storage/app/scratch/1-2-8d2ac06b-60f4-4879-9e5c-819e929003f7/tmp";
        $exportDir = "/home/vagrant/sites/biospexProd/storage/app/exports/1-2-8d2ac06b-60f4-4879-9e5c-819e929003f7.tar.gz";
        $tmpTar = "/tmp/1-2-8d2ac06b-60f4-4879-9e5c-819e929003f7.tar";
        $tmpGz = "/tmp/1-2-8d2ac06b-60f4-4879-9e5c-819e929003f7.tar.gz";

        //$fi = new \FilesystemIterator($tmpDir);

        try {
            $archive = new \PharData($tmpTar);
            $archive->buildFromIterator(new \DirectoryIterator($tmpDir), $tmpDir);
            $archive->compress(Phar::GZ);
            \File::delete($tmpTar);
            \File::move($tmpGz, $exportDir);

        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }


        /*
        foreach ($fi as $file) {
            dd($file);
        }
        */

        // compress to "outfile.tar.gz
        //$gzipped = $archive->compress(Phar::GZ);

        // delete outfile.tar
        //unset($archive);
        //unlink("outfile.tar");

        //$cmd = "cd /home/vagrant/sites/biospexProd/storage/app/scratch/1-2-8d2ac06b-60f4-4879-9e5c-819e929003f7/tmp && sudo tar -czf /home/vagrant/sites/biospexProd/storage/app/exports/1-2-8d2ac06b-60f4-4879-9e5c-819e929003f7.tar.gz *";
        //$cmd = "cd /home/vagrant/sites/biospexProd/storage/app/scratch/1-2-8d2ac06b-60f4-4879-9e5c-819e929003f7/tmp && tar -czf /tmp/1-2-8d2ac06b-60f4-4879-9e5c-819e929003f7.tar.gz *";

    }
}