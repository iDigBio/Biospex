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

use App\Jobs\NfnClassificationsTranscriptJob;
use File;
use Illuminate\Console\Command;

class NfnClassificationsTranscript extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:transcript {expeditionIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process reconciled transcriptions and enter into database.';

    /**
     * NfNClassificationsCsvRequests constructor.
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
        $expeditionIds = null === $this->argument('expeditionIds') ? $this->readDirectory() : explode(',', $this->argument('expeditionIds'));
        NfnClassificationsTranscriptJob::dispatch($expeditionIds);
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