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

use App\Jobs\DwcFileImportJob;
use App\Repositories\ImportRepository;
use Illuminate\Console\Command;

/**
 * Class DarwinCoreFileImportCommand
 *
 * @package App\Console\Commands
 */
class DarwinCoreFileImportCommand extends Command
{

    /**
     * @var \App\Repositories\ImportRepository
     */
    private $importRepo;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'dwc:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to re-queue dwc import after a failure.";

    /**
     * DarwinCoreFileImportCommand constructor.
     * 
     * @param \App\Repositories\ImportRepository $importRepo
     */
    public function __construct(ImportRepository $importRepo)
    {
        parent::__construct();

        $this->importRepo = $importRepo;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $import = $this->importRepo->findBy('error', 0);

        if ($import === null)
            return;

        DwcFileImportJob::dispatch($import);

        echo "Import added to Queue." . PHP_EOL;

    }
}
