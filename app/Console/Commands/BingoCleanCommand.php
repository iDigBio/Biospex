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

use App\Models\BingoUser;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

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
    protected $description = 'Remove expired bingo users.';

    public function __construct(protected BingoUser $bingoUser)
    {
        parent::__construct();
    }

    /**
     * Handle job
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        $records = $this->bingoUser->where('created_at', '<', Carbon::now()->subDays(1))->get();

        $records->each(function ($record) {
            $record->delete();
        });
    }
}
