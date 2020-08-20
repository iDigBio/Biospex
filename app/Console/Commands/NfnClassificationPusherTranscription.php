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

use File;
use App\Jobs\NfnClassificationPusherTranscriptionJob;
use Illuminate\Console\Command;
use Storage;

class NfnClassificationPusherTranscription extends Command
{
    /**
     * The name and signature of the console command.
     * expeditionIds are comma delimited expedition expeditionIds.
     *
     * @var string
     */
    protected $signature = 'nfn:pusher {expeditionIds?} {--C|command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run WeDigBio dashboard to create/update records';

    /**
     * PusherTranscription constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $command = $this->option('command');
        $expeditionIds = null === $this->argument('expeditionIds') ? $this->readDirectory() : explode(',', $this->argument('expeditionIds'));

        foreach ($expeditionIds as $expeditionId) {
            NfnClassificationPusherTranscriptionJob::dispatch($expeditionId, $command);
        }
    }

    /**
     * Read directory files to process.
     */
    private function readDirectory()
    {
        $expeditionIds = [];
        $files = File::files(Storage::path(config('config.nfn_downloads_transcript')));

        foreach ($files as $file)
        {
            $expeditionIds[] = basename($file, '.csv');
        }

        return $expeditionIds;
    }
}
