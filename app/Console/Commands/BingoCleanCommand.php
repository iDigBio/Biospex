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

use App\Repositories\BingoMapRepository;
use Illuminate\Console\Command;

/**
 * Class BingoCleanCommand
 */
class BingoCleanCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'bingo:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired bingo maps.';

    /**
     * @var \App\Repositories\BingoMapRepository
     */
    private $bingoMapRepo;

    /**
     * BingoCleanCommand constructor.
     */
    public function __construct(BingoMapRepository $bingoMapRepo)
    {
        parent::__construct();
        $this->bingoMapRepo = $bingoMapRepo;
    }

    /**
     * Handle job
     *
     * @throws \Exception
     */
    public function handle()
    {
        $records = $this->bingoMapRepo->getBingoMapForCleaning();

        $records->each(function ($record) {
            $record->delete();
        });
    }
}
