<?php
/**
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

use App\Jobs\NfnClassificationsUpdateJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class NfnClassificationsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:update {expeditionIds?} {--files=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update NfN Classifications for Expeditions. Argument is comma separated expeditionIds.';


    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expeditionIds = $this->argument('expeditionIds') === null ?
            null : explode(',', $this->argument('expeditionIds'));

        $files = $this->option('files');

        if ($expeditionIds === null && $files === null)
        {
            return;
        }

        $expeditionIds = $files !== "true" ? $expeditionIds : $this->readDirectory();

        collect($expeditionIds)->each(function ($expeditionId){
            NfnClassificationsUpdateJob::dispatch($expeditionId);
        });
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $expeditionIds = [];
        $files = File::files(config('config.nfn_downloads_transcript'));
        foreach ($files as $file)
        {
            $expeditionIds[] = basename($file, '.csv');
        }

        return $expeditionIds;
    }
}
