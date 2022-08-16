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

use App\Models\Expedition;
use App\Repositories\ExpeditionRepository;
use App\Services\Actor\ActorFactory;
use Illuminate\Console\Command;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        ExpeditionRepository $expeditionRepository
    ) {
        parent::__construct();
        $this->expeditionRepository = $expeditionRepository;
    }

    /**
     *
     */
    public function handle()
    {

        $expedition = $this->expeditionRepository->findwith(422, ['nfnActor', 'stat']);
        ActorFactory::create($expedition->nfnActor->class)->actor($expedition->nfnActor);

    }
}