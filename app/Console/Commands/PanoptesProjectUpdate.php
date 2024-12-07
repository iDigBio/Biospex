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

use App\Jobs\PanoptesProjectUpdateJob;
use App\Repositories\PanoptesProjectRepository;
use Illuminate\Console\Command;

/**
 * Class PanoptesProjectUpdate
 */
class PanoptesProjectUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panoptes:project {expeditionIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update expedition panoptes_projects. Accepts comma separated ids or empty.';

    private $expeditionIds;

    /**
     * PanoptesProjectUpdate constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(PanoptesProjectRepository $panoptesProjectRepo)
    {
        $this->setIds();

        $projects = $this->expeditionIds === null ?
            $panoptesProjectRepo->all() :
            $panoptesProjectRepo->whereIn('expedition_id', $this->expeditionIds);

        $projects->each(function ($project) {
            PanoptesProjectUpdateJob::dispatch($project);
        });
    }

    /**
     * Set expedition ids if passed via argument.
     */
    private function setIds()
    {
        $this->expeditionIds = $this->argument('expeditionIds') === null ? null :
            explode(',', $this->argument('expeditionIds'));
    }
}
