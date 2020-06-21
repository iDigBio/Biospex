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

use App\Repositories\Interfaces\BingoMap;
use Illuminate\Console\Command;

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
    protected $description = "Remove expired bingo maps.";

    /**
     * @var \App\Repositories\Interfaces\BingoMap
     */
    private $bingoMapContract;

    /**
     * BingoCleanCommand constructor.
     *
     * @param \App\Repositories\Interfaces\BingoMap $bingoMapContract
     */
    public function __construct(BingoMap $bingoMapContract)
    {
        parent::__construct();
        $this->bingoMapContract = $bingoMapContract;
    }

    /**
     * Handle job
     *
     * @throws \Exception
     */
    public function handle()
    {
        $records = $this->bingoMapContract->getBingoMapForCleaning();

        $records->each(function($record){
            $record->delete();
        });
    }
}



