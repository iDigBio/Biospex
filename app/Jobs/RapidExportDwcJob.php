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

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobErrorNotification;
use App\Services\Export\RapidExportDwc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RapidExportDwcJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $filePath;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * RapidExportDwcJob constructor.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->onQueue(config('config.rapid_tube'));
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RapidExportDwc $rapidExportDwc)
    {
        try {
            $rapidExportDwc->process($this->key);
        } catch (\Exception $e) {
            $attributes = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];

            $user = User::find(1);
            $user->notify(new JobErrorNotification($attributes));

            $this->delete();
        }
    }
}
