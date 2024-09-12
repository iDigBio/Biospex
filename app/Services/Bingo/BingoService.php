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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Bingo;

use App\Models\Bingo;
use App\Models\BingoMap;
use App\Models\BingoWord;
use App\Models\User;
use App\Services\Api\GeoPlugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JavaScript;
use Session;

/**
 * Class BingoService
 */
class BingoService
{
    /**
     * BingoService constructor.
     */
    public function __construct(
        public Bingo $bingo,
        public BingoMap $bingoMap,
        public BingoWord $bingoWord,
        public GeoPlugin $location
    ) {}

    /**
     * Get bingo with relations by user id.
     */
    public function getAdminIndex(User $user): Collection
    {
        return $user->isAdmin() ?
            $this->bingo->with(['user', 'project', 'words'])->orderBy('created_at', 'desc')->get() :
            $this->bingo->with(['user', 'project', 'words'])->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create bingo.
     */
    public function createBingo(array $attributes): mixed
    {
        $bingo = $this->bingo->create($attributes);

        $words = collect($attributes['words'])->map(function ($word) {
            $values = [
                'word' => $word['word'],
                'definition' => $word['definition'],
            ];

            return $this->bingoWord->make($values);
        });

        $bingo->words()->saveMany($words->all());

        return $bingo;
    }

    /**
     * Update bingo.
     */
    public function updateBingo(Bingo $bingo, array $attributes): Bingo
    {
        $bingo->fill($attributes)->save();

        collect($attributes['words'])->each(function ($word) {
            $record = $this->bingoWord->find($word['id']);
            $record->word = $word['word'];
            $record->definition = $word['definition'];
            $record->save();
        });

        return $bingo;
    }

    /**
     * Create bingo map. Default Tallahassee if lat/long empty.
     */
    private function createBingoMap(Bingo $bingo): Model|BingoMap
    {
        $values = [
            'bingo_id' => $bingo->id,
            'ip' => $this->location->ip,
            'latitude' => $this->location->latitude == null ? '30.43826' : $this->location->latitude,
            'longitude' => $this->location->longitude == null ? '-84.28073' : $this->location->longitude,
            'city' => $this->location->city == null ? 'Tallahassee' : $this->location->city,
        ];

        return $bingo->maps()->create($values);
    }

    /**
     * Show bingo page.
     */
    public function showPublicBingo(Bingo $bingo): array
    {
        $bingo->load(['words', 'user', 'project']);

        $words = $bingo->words->chunk(3);

        return [$bingo, $words];
    }

    /**
     * Generate bingo card.
     */
    public function generateBingoCard(Bingo $bingo): Collection
    {
        $this->location->locate();

        $bingoMap = $this->buildOrReturnMap($bingo);

        JavaScript::put([
            'channel' => config('config.poll_bingo_channel').'.'.$bingo->uuid,
            'winnerUrl' => route('front.get.bingo-winner', [$bingo, $bingoMap]),
            'mapUuid' => $bingoMap->uuid,
        ]);

        $words = $bingo->words->pluck('definition', 'word')->shuffleWords();
        $words->splice(12, 0, [['logo', '']]);

        $i = 1;

        return $words->chunk(5)->map(function ($row) use (&$i) {
            $collection = collect(['a'.$i, 'b'.$i, 'c'.$i, 'd'.$i, 'e'.$i]);
            $i++;

            return $collection->combine($row)->toArray();
        });
    }

    /**
     * Find bingo map by uuid.
     */
    private function buildOrReturnMap(Bingo $bingo): BingoMap
    {
        $bingoMap = Session::get('bingoUuid') === null ?
            $this->createBingoMap($bingo) :
            $this->bingoMap->where('uuid', Session::get('bingoUuid'))->first();

        Session::put('bingoUuid', $bingoMap->uuid);

        return $bingoMap;
    }
}
