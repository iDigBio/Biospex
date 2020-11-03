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

namespace App\Jobs;

use App\Events\BingoEvent;
use App\Services\Model\BingoMapService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BingoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $bingoId;

    /**
     * @var string|null
     */
    private $mapId;

    /**
     * BingoJob constructor.
     *
     * @param string $bingoId
     * @param string|null $mapId
     */
    public function __construct(string $bingoId, string $mapId = null)
    {
        $this->bingoId = $bingoId;
        $this->mapId = $mapId;
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Job handle.
     *
     * @param \App\Services\Model\BingoMapService $bingoMapService
     */
    public function handle(BingoMapService $bingoMapService)
    {
        $locations = $bingoMapService->getBy('bingo_id', $this->bingoId);
        $data['markers'] = $locations->map(function($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'city' => $location->city
            ];
        })->toArray();

        $data['winner'] = null;
        if ($this->mapId !== null) {
            $map = $bingoMapService->find($this->mapId);
            $data['winner']['city'] = $map->city;
            $data['winner']['uuid'] = $map->uuid;
        }


        BingoEvent::dispatch($this->bingoId, $data);
    }
}
