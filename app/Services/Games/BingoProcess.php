<?php declare(strict_types = 1);
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

namespace App\Services\Games;

use App\Models\Bingo;
use App\Models\BingoMap;
use App\Services\Api\GeoPlugin;
use General;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JavaScript;
use Session;

/**
 * Class BingoProcess
 *
 * @package App\Services\Process
 */
readonly class BingoProcess
{
    /**
     * BingoProcess constructor.
     *
     * @param \App\Models\Bingo $bingo
     * @param \App\Models\BingoMap $bingoMap
     * @param \App\Services\Api\GeoPlugin $location
     */
    public function __construct(private Bingo $bingo, private BingoMap $bingoMap, private GeoPlugin $location)
    {}

    /**
     * Get all bingo games.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllBingos(): Collection
    {
        return $this->bingo->with(['user', 'project'])->get();
    }

    /**
     * Find bingo resource.
     *
     * @param string $id
     * @param array $with
     * @return \App\Models\Bingo
     */
    public function findBingoWith(string $id, array $with = []): Bingo
    {
        return $this->bingo->with($with)->find($id);
    }

    /**
     * Find bingo map by uuid.
     *
     * @param \App\Models\Bingo $bingo
     * @param string $uuid
     * @return \App\Models\BingoMap
     */
    private function findBingoMapByUuid(Bingo $bingo, string $uuid): BingoMap
    {
        $map = $this->bingoMap->where('bingo_id', $bingo->id)->where('uuid', $uuid)->first();

        if ($map === null) {
            $map = $this->createBingoMap($bingo);
        }

        return $map;
    }

    /**
     * Create bingo map. Default Tallahassee if lat/long empty.
     *
     * @param \App\Models\Bingo $bingo
     * @return \App\Models\BingoMap|\Illuminate\Database\Eloquent\Model
     */
    private function createBingoMap(Bingo $bingo): Model|BingoMap
    {
        $values = [
            'bingo_id' => $bingo->id,
            'ip' => $this->location->ip,
            'latitude' => $this->location->latitude == null ? '30.43826' : $this->location->latitude,
            'longitude' => $this->location->longitude == null ? '-84.28073' : $this->location->longitude,
            'city' => $this->location->city == null ? 'Tallahassee' : $this->location->city
        ];

        return $bingo->maps()->create($values);
    }

    /**
     * Show bingo page.
     *
     * @param string $bingoId
     * @return array
     */
    public function showBingo(string $bingoId): array
    {
        $bingo = $this->bingo->with(['words', 'user', 'project'])->find($bingoId);

        $words = is_null($bingo) ? null : $bingo->words->chunk(3);

        return [$bingo, $words];
    }

    /**
     * Generate bingo card.
     *
     * @param \App\Models\Bingo $bingo
     * @return \Illuminate\Support\Collection
     */
    public function generateBingoCard(Bingo $bingo): Collection
    {
        $this->location->locate();

        $uuid = General::uuidToBin(Session::get('bingoUuid'));

        $map = $uuid === null ? $this->createBingoMap($bingo) : $this->findBingoMapByUuid($bingo, $uuid);

        Session::put('bingoUuid', $map->uuid);

        JavaScript::put([
            'channel' => config('config.poll_bingo_channel') . '.' . $bingo->id,
            'winnerUrl' => route('ajax.get.bingoWinner', ['bingo' => 1, 'map' => $map->id]),
            'mapUuid' => $map->uuid
        ]);

        $words = $bingo->words->pluck('definition', 'word')->shuffleWords();
        $words->splice(12, 0, [['logo', '']]);

        $i = 1;
        return $words->chunk(5)->map(function($row) use (&$i) {
            $collection = collect(['a'.$i, 'b'.$i, 'c'.$i, 'd'.$i, 'e'.$i]);
            $i++;

            return $collection->combine($row)->toArray();
        });
    }
}