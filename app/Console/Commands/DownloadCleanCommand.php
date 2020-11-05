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
use Illuminate\Filesystem\Filesystem;
use App\Services\Model\DownloadService;
use Illuminate\Support\Facades\Storage;

class DownloadCleanCommand extends Command
{

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var \App\Services\Model\DownloadService
     */
    public $downloadService;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'download:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove expired download files.";

    /**
     * Directory where nfn downloads are stored.
     *
     * @var string
     */
    protected $nfnExportDir;

    /**
     * DownloadCleanCommand constructor.
     *
     * @param Filesystem $filesystem
     * @param \App\Services\Model\DownloadService $downloadService
     */
    public function __construct(
        Filesystem $filesystem,
        DownloadService $downloadService
    )
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->downloadService = $downloadService;

        $this->nfnExportDir = Storage::path(config('config.export_dir'));
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $downloads = $this->downloadService->getDownloadsForCleaning();

        $downloads->each(function ($download)
        {
            $file = $this->nfnExportDir . '/' . $download->file;
            if ($this->filesystem->isFile($file))
            {
                $this->filesystem->delete($file);
            }

            $download->delete();
        });

        $files = collect($this->filesystem->files($this->nfnExportDir));
        $files->each(function($file){
            $fileName = $this->filesystem->basename($file);
            $result = $this->downloadService->findBy('file', $fileName);
            if ( ! $result)
            {
                $this->filesystem->delete($file);
            }
        });

    }
}
