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

use App\Events\BingoEvent;
use App\Models\Bingo;
use App\Models\City;
use App\Services\Api\GeoPlugin;
use App\Services\Bingo\BingoService;
use App\Services\Csv\Csv;
use Illuminate\Console\Command;

/**
 * Class AppCommand
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
     * Create a new command instance.
     */
    public function __construct(protected BingoService $bingoService, protected Csv $csv, protected GeoPlugin $location)
    {
        parent::__construct();
    }

    /**
     * @return void
     *
     * @throws \League\Csv\Exception
     */
    public function handle()
    {
        $this->location->locate();
        dd($this->location->city);

        return;

        $this->csv->readerCreateFromPath(storage_path('worldcities.csv'));
        $this->csv->setHeaderOffset();
        $rows = $this->csv->getRecords();
        foreach ($rows as $row) {
            City::create([
                'city' => $row['city'],
                'latitude' => $row['lat'],
                'longitude' => $row['lng'],
            ]);
        }

        return;

        //dd(\Str::uuid());
        $bingo = Bingo::find(7);
        $locations = $this->bingoService->getMapLocations($bingo->id);

        $data = [];

        $data['markers'] = $locations->map(function ($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'city' => $location->city,
            ];
        })->toArray();

        $data['winner'] = null;

        \Log::info('BingoJob', $data);

        BingoEvent::dispatch($bingo, json_encode($data));
    }
}
