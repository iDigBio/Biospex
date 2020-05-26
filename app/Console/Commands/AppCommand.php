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

use App\Facades\GeneralHelper;
use App\Models\Bingo;
use App\Repositories\Interfaces\BingoMap;
use App\Services\Api\GeoLocation;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\BingoMap
     */
    private $bingoMapContract;

    /**
     * @var \App\Services\Api\GeoLocation
     */
    private $location;

    /**
     * AppCommand constructor.
     */
    public function __construct(BingoMap $bingoMapContract, GeoLocation $location) {
        parent::__construct();

        $this->bingoMapContract = $bingoMapContract;
        $this->location = $location;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $test = $this->bingoMapContract->findBy('ip', '68.63.24.33');
        $uuid = GeneralHelper::uuidToBin($test->uuid);
        $result = $this->bingoMapContract->findBy('uuid', $uuid);
        dd($result);
        $bingo = Bingo::find(1);

        // temp since working local
        $this->location->locate('68.63.24.33');

        $attributes = [
            'bingo_id' => $bingo->id,
            'uuid' => \Session::get('bingoUuid') ?? null,
            'ip' => $this->location->ip
        ];
        $values = [
            'bingo_id' => $bingo->id,
            'uuid' => \Session::get('bingoUuid') ?? null,
            'ip' => $this->location->ip,
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'city' => $this->location->city
        ];

        $map = $bingo->maps()->firstOrCreate($attributes, $values);
        dd($map);
    }
}