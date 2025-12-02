<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Models\OcrQueue;
use App\Models\OcrQueueFile;
use Illuminate\Console\Command;

class AppTestCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     */
    protected $description = 'Simulate OCR Process for Queue';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $queueId = 53;
        $this->info("Looking for Queue ID: $queueId");

        $queue = OcrQueue::find($queueId);

        if (! $queue) {
            $this->error('Queue not found!');

            return;
        }

        $this->info('Found Queue. Resetting processed files...');

        // Reset counts
        OcrQueueFile::where('queue_id', $queueId)->update(['processed' => 0]);

        // Verify reset
        $total = OcrQueueFile::where('queue_id', $queueId)->count();
        $queue->total = $total;
        $queue->stage = 2; // Running
        $queue->save();

        $this->info("Queue reset. Total files: $total");
        $this->info('Switch to your browser NOW. Starting simulation in 5 seconds...');

        sleep(5);

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $files = OcrQueueFile::where('queue_id', $queueId)->get();

        foreach ($files as $file) {
            // Simulate processing
            $file->processed = 1;
            $file->save();

            $bar->advance();

            // Wait 2 seconds to allow browser polling to catch it
            sleep(2);
        }

        $bar->finish();
        $this->newLine();
        $this->info('Simulation Complete.');
    }
}
