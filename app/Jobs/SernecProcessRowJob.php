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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\Subject;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class for processing rows and performing operations based on provided data.
 */
class SernecProcessRowJob
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Constructor method to initialize the object with the provided data.
     *
     * @param  array  $data  An array of data to be used for initialization.
     * @return void
     */
    public function __construct(protected array $data) {}

    /**
     * Handles the execution of the operation to update a subject's access URI based on provided data.
     */
    public function handle(): void
    {
        $identifier = basename($this->data[1]);
        $subject = Subject::where('accessURI', 'LIKE', "%$identifier%")->first();
        if ($subject) {
            $subject->update(['accessURI' => $this->data[2], 'oldAccessURI' => $this->data[1]]);
        }
    }
}
