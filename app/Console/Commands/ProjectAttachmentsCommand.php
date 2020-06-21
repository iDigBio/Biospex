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

use App\Repositories\Interfaces\Project;
use Illuminate\Console\Command;
use Storage;

class ProjectAttachmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:attachments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * Create a new command instance.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     */
    public function __construct(Project $projectContract)
    {
        parent::__construct();
        $this->projectContract = $projectContract;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projects = $this->projectContract->all();

        $projects->each(function($project){
            if ( ! $this->variantExists($project->logo)) {
                $project->logo->setToBeDeleted();
            }

            if ( ! $this->variantExists($project->banner)) {
                $project->banner->setToBeDeleted();
            }

            $project->save();
        });
    }

    /**
     * @param $attachment
     * @param null $variant
     * @return bool
     */
    public function variantExists($attachment, $variant = null)
    {
        return $attachment->exists() && Storage::disk('public')->exists($attachment->variantPath($variant));
    }
}
