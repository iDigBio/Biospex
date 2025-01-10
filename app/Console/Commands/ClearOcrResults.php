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

use App\Models\Subject;
use Artisan;
use Illuminate\Console\Command;

/**
 * Class ClearOcrResults
 */
class ClearOcrResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:clear {projectId} {expeditionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear project expeditions ocr values.';

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
        $projectId = $this->argument('projectId');
        $expeditionId = $this->argument('expeditionId');

        $subjects = Subject::where('project_id', (int) $projectId)->limit(50)->get();
        foreach ($subjects as $subject) {
            $subject->ocr = '';
            $subject->save();
        }

        Artisan::call('lada-cache:flush');
    }
}
